<?php

namespace App\Http\Controllers\Multi\Auth;

use App\Http\Controllers\Controller;
use App\Mail\DocumentTrackerEmail;
use App\Mail\NewPasswordMail;
use App\Models\Access\User\User;
use App\Repositories\Frontend\Access\User\UserRepository;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Class ResetPasswordController.
 */
class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * ChangePasswordController constructor.
     *
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * Where to redirect users after resetting password.
     *
     * @return string
     */
    public function redirectPath()
    {
        return route('frontend.index');
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param string|null $token
     *
     * @return \Illuminate\Http\Response
     */
    public function showResetForm($token = null)
    {
        if (!$token) {
            return redirect()->route('frontend.auth.password.email');
        }

        $user = $this->user->findByPasswordResetToken($token);
        $userId = $user->id;

        if ($user && app()->make('auth.password.broker')->tokenExists($user, $token)) {
            return view('core.auth.passwords.reset', compact('userId'))->withForm(['route' => 'frontend.auth.send-new-password', 'class' => 'form-horizontal'])
                ->withToken($token)
                ->withEmail($user->email);
        }

        return redirect()->route('frontend.auth.password.email')
            ->withFlashDanger(trans('exceptions.frontend.auth.password.reset_problem'));
    }


    public function sendNewPasswordEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:64',
            'user_id' => 'required|integer|exists:users,id',
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::withoutGlobalScopes()->find($request->user_id);

        $upperCase = Str::upper(Str::random(2));
        $lowerCase = Str::lower(Str::random(4));
        $numbers = implode('', array_rand(array_flip(range(0, 9)), 2));
        $specialCharacters = implode('', array_rand(array_flip(['@', '#', '$', '%', '&', '*', '!']), 2));

        $password = str_shuffle($upperCase . $lowerCase . $numbers . $specialCharacters);

        $user->password = $password; // Mutator automatically hashes it
        if (!$user->save()) {
            Log::error('Failed to save password for user: ' . $user->id);
            return response()->json(['error' => 'Password update failed.'], 500);
        }

        Mail::to($user->email)->send(new NewPasswordMail($password));

        return view('core.auth.passwords.successful-reset');
    }


    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed|regex:"^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"',
        ];
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [
            'password.regex' => 'Password must contain at least 1 uppercase letter and 1 number.',
        ];
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse($request, $response)
    {
        return redirect()->route('biller.index')->withFlashSuccess(trans($response));
    }

}
