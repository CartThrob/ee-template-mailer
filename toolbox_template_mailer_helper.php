<?php 

if (!class_exists('Toolbox_Template_Mailer'))
{
    require_once APPPATH . 'libraries/Template.php';
    
    /**
     * Lets you easily send an email that uses an EE template as the message body
     *
     * @package Toolbox
     * @author Thomas Brewer
     **/
    class Toolbox_Template_Mailer {

        public $EE = NULL;

        public $to = '';

        public $from = '';

        public $subject = '';

        public $message = '';

        public $bcc = ''; 

        public $email_data = array();

        /**
         * __construct
         *
         * @access public
         * @param  string $to
         * @param  string $from
         * @param  string $subject (optional)   
         * @param  string $bcc (optional)   
         * @return void
         * 
         **/
        public function __construct($to, $from, $subject = '', $bcc = NULL) 
        {    
            $this->EE = get_instance();

            $this->EE->TMPL = new EE_Template();

            $this->to = $to;

            $this->from = $from;

            $this->subject = $subject;

            $this->bcc = $bcc;  

            $this->EE->load->library('email');  

            $this->EE->load->helper('text'); 

            $this->EE->email->wordwrap = true;

            $this->EE->email->mailtype = 'html'; 
        } 

        /**
         *  set
         *
         * @access public
         * @param  string $key  
         * @param  string $value
         * @return void
         * 
         **/
        public function set($key, $value = NULL) 
        {                     
            if (is_array($key))
            {
                $this->email_data = array_merge($this->email_data, $key);
            }   
            else
            {
                $this->email_data[$key] = $value;
            }
        }

        /**
         * template
         *
         * @access public
         * @param  string $template 
         * @return void
         * 
         **/
        public function template($template) 
        {
            if (!empty($template))
            {
                foreach ($this->email_data as $key => $value) 
                {
                    $this->EE->config->_global_vars[$key] = $value;
                }  

                //needed to be able to access .hidden templates
                $this->EE->TMPL->depth = 1;

                list($template_group, $template_name) = explode('/', $template);

                $this->EE->TMPL->fetch_and_parse($template_group, $template_name, FALSE);

                $this->EE->TMPL->cache_status = 'NO_CACHE';

                $template = $this->EE->TMPL->fetch_template($template_group, $template_name, FALSE, $this->EE->config->item('site_id'));

                $this->EE->TMPL->parse($template, FALSE, $this->EE->config->item('site_id'));

                $this->message = $this->EE->TMPL->final_template;

                $this->message = $this->EE->TMPL->parse_globals($this->message);

                //clean up
                $this->EE->TMPL->depth = 0;

                foreach (array_values($this->email_data) as $key) 
                {
                    unset($this->EE->config->_global_vars[$key]);    
                }
            }
        }

        /**
         * send
         *
         * @access public
         * @param  string $template 
         * @return bool
         * 
         **/
        public function send($template = NULL) 
        {   
            //are we passing a template to the send method?
            if ($template !== NULL)
            {
                $this->template($template);
            }

            //if we have no message we cannot send - so bail out...
            if (empty($this->message))
            {
                return FALSE;
            }

            $this->EE->email->to($this->to); 

            if (is_array($this->from) AND count($this->from) == 2)
            {
                $this->EE->email->from($this->from[0], $this->from[1]);
            }
            else if (!is_array($this->from))
            {
                $this->EE->email->from($this->from);
            }

            if ($this->bcc !== NULL)
            {   
                $this->EE->email->bcc($this->bcc);
            }

            $this->EE->email->subject($this->subject);
            $this->EE->email->message(entities_to_ascii($this->message));

            return $this->EE->email->Send();
        }
    }
}