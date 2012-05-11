<?php

class Template_mailer {
    
    /**
     * @var  object
     */
    protected $EE;
    
    /**
     * @var  array
     */
    protected $vars = array();
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->EE =& get_instance();
        
        if( ! isset($this->EE->TMPL))
        {
            $this->EE->load->library('template', NULL, 'TMPL');
        }
        
        $this->EE->load->library('email');
        
        $this->EE->email->initialize(array(
            'mailtype' => 'html',
        ));
    }
    
    /**
     * Magic method for function calls
     *
     * @param   mixed
     * @param   array
     * @return  mixed|void
     */
    public function __call($name, $args)
    {
        $name = strtolower($name);
        
        $valid_methods = array(
            'from',
            'reply_to',
            'to',
            'cc',
            'bcc',
            'subject',
            'set_alt_message',
            'attach',
            'print_debugger'
        );
        
        // if the method name maps to the email class, call it
        if(in_array($name, $valid_methods))
        {
            $result = call_user_func_array(array($this->EE->email, $name), $args);
            
            if($result !== NULL)
            {
                return $result;
            }
        }
    }
    
    /**
     * Set variables for tags in the email body
     *
     * @param  string
     * @param  mixed
     */
    public function set($name, $value = NULL)
    {
        if(is_array($name))
        {
            $this->vars = array_merge($this->vars, $name);
        }
        else
        {
            $this->vars[$name] = $value;
        }
    }
    
    /**
     * Sets the template to use for the message copy
     *
     * @param   string
     * @return  string
     */
    protected function template($template)
    {
        // split the template group and name
        $segments = explode('/', $template);
        
        // use index as name if not specified
        if(count($segments) == 1)
        {
            $segments[1] = 'index';
        }
        
        list($template_group, $template_name) = $segments;
        
        // set data items as global variables
        foreach($this->vars as $key => $val)
        {
            $this->EE->config->_global_vars['email:'.$key] = $val;
        }
        
        // parse the template
        $this->EE->TMPL->cache_status = 'NO_CACHE';
        $template = $this->EE->TMPL->fetch_template($template_group, $template_name, FALSE);
        $body = $this->EE->TMPL->parse_globals($template);
        $body = $this->EE->TMPL->parse_variables_row($body, $this->vars);
        $this->EE->TMPL->parse($body, FALSE);
        $body = $this->EE->TMPL->final_template;
        
        // unset data items
        foreach($this->vars as $key => $val)
        {
            unset($this->EE->config->_global_vars['email:'.$key]);
        }
        
        return $body;
    }
    
    /**
     * Sends the email
     *
     * @param   string|array|null
     * @return  bool
     */
    public function send($template)
    {
        // get template output
        $body = $this->template($template);
        
        // set message
        $this->EE->email->message($body);
        
        // send the email
        $result = $this->EE->email->send();
        
        $this->EE->email->clear();
        
        return $result;
    }
    
}