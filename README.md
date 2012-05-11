# Toolbox Template Mailer Helper

This helper is used with a module or extension to send emails that use EE templates.  This supports PHP, globals, and entries loops.

_Developed by [eecoder](http://eecoder.com)._

## Usage

* To get started drop the file in a **helpers** directory in your module or extension.

		$this->EE->load->library('template_mailer');		            		$email = new Template_mailer();
				$email->to($this->EE->config->item('webmaster_email'));		$email->from($this->EE->config->item('webmaster_email'));		$email->subject('Subject');		$email->set(array(			'email_address' => 'email@domain.com',		));		$result = $email->send('_messages/email-template');		
`$email->set()` creates variables for use in the email template, and are prepended with `email:`.  The variable above would be `{email:email_address}`