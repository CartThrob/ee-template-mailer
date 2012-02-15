# Toolbox Template Mailer Helper

This helper is used with a module or extension to send emails that use EE templates.  This supports PHP, globals, and entries loops.

_Developed by [eecoder](http://eecoder.com)._

## Usage

* To get started drop the file in a **helpers** directory in your module or extension.
* Load the helper with `$this->EE->load->helper('Toolbox_Template_Mailer');`

		$email_data = array(
			'title' => $query->row('title')
		);
		
		$mailer = new Toolbox_Template_Mailer(
			$query->row('email'),
			array($this->EE->config->item('webmaster_email'), 'From Name'),
			'Subject',
			'BCC'
		);
		
		$mailer->set($email_data);
		$mailer->template('_messages/.mailer-template');
		
		$mailer->send();



