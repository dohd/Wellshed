<?php

namespace App\Repositories\Focus\message_template;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\message_template\MessageTemplate;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Class MessageTemplateRepository.
 */
class MessageTemplateRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = MessageTemplate::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        $input = array_map( 'strip_tags', $input);
        $message_template = MessageTemplate::where(['type'=> $input['type'], 'user_type'=>$input['user_type']])->first();
        if($message_template) throw ValidationException::withMessages(['Message Template of User Type '.str_replace('_',' ',$input['type']).' already exists']);
        if (MessageTemplate::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.MessageTemplates.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param MessageTemplate $MessageTemplate
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(MessageTemplate $message_template, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($message_template->update($input))
            return true;

        throw new GeneralException('Error Updating Message Template');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param MessageTemplate $MessageTemplate
     * @throws GeneralException
     * @return bool
     */
    public function delete(MessageTemplate $message_template)
    {
        if ($message_template->delete()) {
            return true;
        }

        throw new GeneralException('Error Deleting Message Template');
    }
}
