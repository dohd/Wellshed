<?php

namespace App\Models\documentManager;

use App\Mail\DocumentTrackerEmail;
use App\Models\hrm\Hrm;
use App\Notifications\DocumentTrackerReminder;
use App\Repositories\Focus\general\RosemailerRepository;
use App\User;
use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class DocumentManager extends Model
{


    protected $table = 'document_manager';

    protected $fillable = [
        'name',
        'document_type',
        'description',
        'responsible',
        'co_responsible',
        'issuing_body',
        'issue_date',
        'cost_of_renewal',
        'renewal_date',
        'expiry_date',
        'alert_days_before',
        'status',
        'created_by',
        'updated_by',
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(Hrm::class, 'responsible', 'id');
    }

    public function coResponsibleUser()
    {
        return $this->belongsTo(Hrm::class, 'co_responsible', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(Hrm::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Hrm::class, 'updated_by');
    }

    /**
     * @throws \Exception
     */
    public static function checkForRenewalReminders()
    {
        $today = new DateTime();
        $documentTrackers = self::all();

        foreach ($documentTrackers as $dT) {

            $expiryDate = new DateTime($dT->expiry_date);
            $renewalDate = new DateTime($dT->renewal_date);

            // Calculate alert date for renewal reminders
            $alertDaysBefore = new DateInterval('P' . $dT->alert_days_before . 'D');
            $alertDate = (clone $renewalDate)->sub($alertDaysBefore);

            $responsible = [$dT->responsibleUser, $dT->coResponsibleUser];

            // Check if document is active
            if ($dT->status === 'ACTIVE') {

                // Check if today is within the alert period for renewal reminders
                if ($today >= $alertDate && $today <= $expiryDate) {

                    foreach ($responsible as $rU){

                        $daysToRenewal = (clone $today)->diff(clone $renewalDate);

                        $daysToRenewalInvert = $daysToRenewal->invert;
                        $daysLeftMessage = '';

                        if ($daysToRenewalInvert) {

                            $daysLeftMessage = 'You are '  . $daysToRenewal->days . " " . ($daysToRenewal->days > 1 ? "days" : "day") .
                                ' past your scheduled renewal date on ' . (new DateTime($dT->renewal_date))->format('jS F Y') .
                                '. Hurry and renew this ' . ucfirst(strtolower($dT->document_type)) . " document before it's too late to do so.";
                        }
                        else {

                            $daysLeftMessage = 'You have '  . $daysToRenewal->days . " " . ($daysToRenewal->days > 1 ? "days" : "day") .
                                ' to your scheduled renewal date on ' . (new DateTime($dT->renewal_date))->format('jS F Y') . '.';
                        }


                        $data = [
                            'name' => $rU->first_name . ' ' . $rU->last_name,
                            'message' => "The Tracked Document: '" . $dT->name . "' is due for renewal before its
                                          expiry date on " . (new DateTime($dT->expiry_date))->format('jS F Y'),
                            'daysLeftMessage' => $daysLeftMessage,
                            'valediction' => 'If already renewed/archived, update the respective Document Tracker to halt this notification.',

                            'documentTracker' => $dT,
                        ];

                        $subject = "Renewal Reminder for Tracked Document '" . $dT->name . "'.";

                        Mail::to($rU->email)->send(new DocumentTrackerEmail($data, $subject));
                    }
                }

                //if today is the expiry date
                if ($today->format('Y-m-d') === $expiryDate->format('Y-m-d')) {

                    $dT->status = 'EXPIRED';
                    $dT->save();

                    foreach ($responsible as $rU){

                        $data = [
                            'name' => $rU->first_name . ' ' . $rU->last_name,
                            'message' => "The Tracked Document: '" . $dT->name . "' is expiring today on " . (new DateTime($dT->expiry_date))->format('jS F Y') . ".",
                            'daysLeftMessage' => '',
                            'valediction' => 'Notifications for ths document tracker will not proceed past this day.',
                            'documentTracker' => $dT,
                        ];

                        $subject = "Expiry Notification for Tracked Document '" . $dT->name . "'.";

                        Mail::to($rU->email)->send(new DocumentTrackerEmail($data, $subject));
                    }
                }

                // Check if today is the renewal date
                if ($today->format('Y-m-d') === $renewalDate->format('Y-m-d')) {

                    foreach ($responsible as $rU){

                        $data = [
                            'name' => $rU->first_name . ' ' . $rU->last_name,
                            'message' => "The Tracked Document: '" . $dT->name . "' is up for renewal today on " . (new DateTime($dT->renewal_date))->format('jS F Y') . ".",
                            'daysLeftMessage' => '',
                            'valediction' => 'Hurry so you can get this done on your planned Renewal Date.',
                            'documentTracker' => $dT,
                        ];

                        $subject = "Renewal Day Notification for Tracked Document '" . $dT->name . "'.";

                        Mail::to($rU->email)->send(new DocumentTrackerEmail($data, $subject));
                    }
                }

//                // Check if today is past the expiry date
//                if ($today > $expiryDate && $dT->status !== 'EXPIRED') {
//
//                    if ($dT->status !== 'ARCHIVED') {
//
//                        $dT->status = 'EXPIRED';
//                        $dT->save();
//                    }
//                }

            }
        }
    }

    public function sendRenewalReminder()
    {
        Notification::send($this->responsibleUser, new DocumentTrackerReminder($this));
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            $instance->created_by = auth()->user()->id;
            $instance->updated_by = auth()->user()->id;
            return $instance;
        });

        static::updating(function ($instance) {

            $instance->updated_by = auth()->user()->id;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('document_manager.ins', '=', auth()->user()->ins);
        });
    }

}
