<?php

namespace App\Http\Controllers\Admin;

use App\EmailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        Session::put('active','emailTemplates');
        $templates = EmailTemplate::orderBy('event_key')->get();
        $title = "Email Templates";
        return view('admin.email_templates.index', compact('templates','title'));
    }

    public function edit($id)
    {
        Session::put('active','emailTemplates');
        $title = "Edit Email Template";
        $template = EmailTemplate::findOrFail($id);
        return view('admin.email_templates.edit', compact('template','title'));
    }

    public function update(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:255',
            'subject' => 'required|string|max:255',
        ]);

        // Parse comma-separated emails into array, filter empty
        $toEmails  = $this->parseEmails($request->input('to_emails'));
        $ccEmails  = $this->parseEmails($request->input('cc_emails'));
        $bccEmails = $this->parseEmails($request->input('bcc_emails'));

        $template->name      = $request->input('name');
        $template->subject   = $request->input('subject');
        $template->to_emails = !empty($toEmails)  ? $toEmails  : null;
        $template->cc_emails = !empty($ccEmails)  ? $ccEmails  : null;
        $template->bcc_emails= !empty($bccEmails) ? $bccEmails : null;
        $template->is_active = $request->has('is_active') ? 1 : 0;
        $template->save();

        return redirect('/admin/email-templates')
            ->with('flash_message_success', 'Email template updated successfully.');
    }

    protected function parseEmails($input)
    {
        if (empty($input)) {
            return [];
        }
        $emails = explode(',', $input);
        $emails = array_map('trim', $emails);
        return array_values(array_filter($emails));
    }
}