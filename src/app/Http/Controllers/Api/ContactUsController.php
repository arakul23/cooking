<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactQuestionRequest;
use App\Jobs\SendContactUsEmail;
use App\Mail\ContactUs;
use App\Models\ContactQuestion;

class ContactUsController extends Controller
{
    public function __construct(readonly private ContactUs $contactUs, readonly private ContactQuestion $contactQuestion)
    {}

    public function handleContactUsRequest(ContactQuestionRequest $request)
    {
        $request = $request->validated();
        $this->contactQuestion->create($request);

        SendContactUsEmail::dispatch($this->contactUs);
    }
}
