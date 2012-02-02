<?php
/**
 * Jquery-ui Autocomplete helpers
 * 
 * @author      Mirko Chialastri 
 * @version     0.1 
 * 
 * Source can be:
 *      1. an array (auto-complete with static choices):
 *         $this->AutoComplete->input('Model.field', null, array('source' => array('Foo', 'Bar') )) 
 *      2. a URL resource:
 *         $this->AutoComplete->input('Model.field', null, array('source' => '/mycontroller/myspecialaction/'))
 *      3. Javascript callback
 *         $this->AutoComplete->input('Model.field', null, array('source' => 'myCallback()'))
 * 
 **/
  
class AutoCompleteHelper extends AppHelper {
    var $version = '1.0';
    var $helpers = array('Html', 'Form', 'Js');
    
    /* Configurations */ 
    /* Jquery-ui-autocomplete acceptable variables */
    var $jQueryUiOptions = array(
        // Options
        'appendTo' => '$(document.body)',
        'disabled' => false,
        'delay' => 300,
        'source' => null,
        'minLength' => 4,
        
        // Events
        'search' => "function() {}",
        'focus' => "function() {}",
        'select' => "function(event, iu) {}",
    );
    
    /**
     * AutoCompleteHelper options 
     * 
     * @todo        Implement it
     */
    var $autoCompleteHelperOptions = array(
        /* Find options */
        'fields' => null,
    );
    /* AutoComplete callbacks */
    var $callbacks = array(
        'success' => "response(data);",
        'beforeSend' => "",
        
    );


    var $session = null;
    var $runtime = array(
        'use_multiple' => false
    );
    
   /**
    * Helper constructor
    * 
    * @param    array   TinySongHelper options
    */
    function __construct($options = array()) {
        parent::__construct($options);

        if (!class_exists('CakeSession')) {
            App::import('Core','Session');
        }
        $this->session = new CakeSession(null, true);
        $this->session->security = 'hight';
        $this->session->start();

        if (is_null($this->jQueryUiOptions['source'])) $this->jQueryUiOptions['source'] = Router::url('/auto_complete/RemoteSources/get', true);
    }


    public function beforeRender() {
        // Loading plugins assets         
         if ($this->runtime['use_multiple']) {
             $this->Html->script('/auto_complete/js/auto-complete-helper.js', false);
         }
    }
    
    /**
     * Render a input element with auto-complete function 
     * 
     * Require Jquery and Jquery-ui.
     * 
     * @param   string      Model.field
     * @param   array       FormHelper options
     * @param   array       JQuery-ui AutoComplete options
     * @return  void
     *   
     * @author  Chialastri Mirko
     * @version 0.1  
     **/
    public function input($field = null, $formHelperOptions = array(), $jQueryUiOptions = array(), $HelperOptions = array() ) {
        if (!$this->checkFieldFormat($field)) return;
        $this->__createWhiteList($field, @$HelperOptions['fields']);

        // Override default jquery-ui-autocomplete options
        $jq_ui = array_merge($this->jQueryUiOptions, $jQueryUiOptions);
        // Build source and update source index
        $jq_ui['source']  = $this->__buildSource($jq_ui['source'].'/'.$this->session->id(), $field);
        // Convert option to Javascript Object
        $auto_complete_options = $this->_buildOptions($jq_ui, array_keys($this->jQueryUiOptions));
        
        $form_element = '#'.Inflector::camelize(str_replace('.', '_', $field));
        $input_source = $this->Form->input($field, $formHelperOptions);
        
        $__autoCompleteSource = "$('{$form_element}').autocomplete($auto_complete_options);";
        $ac = $this->Js->buffer($__autoCompleteSource);            
        return $this->output("$input_source\n$ac");
    }
    
    
    /**
     * Render a input element with auto-complete function 
     * 
     * Require Jquery and Jquery-ui.
     * 
     * @param   string      Model.field
     * @param   array       FormHelper options
     * @param   array       JQuery-ui AutoComplete options
     * @return  none
     *   
     * @author  Chialastri Mirko
     * @version 0.1  
     **/
    public function multiple($field = null, $formHelperOptions = array(), $jQueryUiOptions = array(), $HelperOptions = array()) {
        if (!$this->checkFieldFormat($field)) return;
        
        $this->__createWhiteList($field, @$HelperOptions['fields']);
        $this->runtime['use_multiple'] = true;
        
        // Override default jquery-ui-autocomplete options
        $jq_ui = array_merge($this->jQueryUiOptions, $jQueryUiOptions);
        
        $jq_ui['search'] = "function() {var term = extractLast(this.value);if ( term.length < 2 ) return false;}";
        $jq_ui['focus'] = "function() {return false;}";
        $jq_ui['select'] = "function(event, ui) {
                    var terms = split(this.value);
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( '' );
                    this.value = terms.join( ', ' );
                    return false;
        }";
        // Edit "term" in data, use only last term (after comma)
        $this->callbacks['beforeSend'] = "var data = tokenize(settings.data, '&');data.term = extractLast(data.term); settings.data = Json2QueryString(data);"; 
                    
        // Build source and update source index
        $jq_ui['source']  = $this->__buildSource($jq_ui['source'], $field);
        // Convert option to Javascript Object
        $auto_complete_options = $this->_buildOptions($jq_ui, array_keys($this->jQueryUiOptions));
        
        $form_element = '#'.Inflector::camelize(str_replace('.', '_', $field));
        $input_source = $this->Form->input($field, $formHelperOptions);
        
        $ac = $this->Html->scriptBlock("$('$form_element').autocomplete($auto_complete_options).bind('keydown', function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( 'autocomplete' ).menu.active ) {
                    event.preventDefault();
                }
            })", array('inline' => 'true'));
        return $this->output("$input_source\n$ac");
    }
    
    
    /* Internal methods */
    
    /**
     * Returns a string of JavaScript with the given option data as a JavaScript options hash.
     *
     * @param array $options    Options in the shape of keys and values
     * @param array $acceptable Array of legal keys in this options context
     * @return string   String of Javascript array definition
     */
    function _buildOptions($options, $acceptable) {
        if (is_array($options)) {
            $out = array();

            foreach ($options as $k => $v) {
                if (in_array($k, $acceptable)) {
                    if ($v === true) {
                        $v = 'true';
                    } elseif ($v === false) {
                        $v = 'false';
                    }
                    $out[] = "$k:$v";
                } elseif ($k === 'with' && in_array('parameters', $acceptable)) {
                    $out[] = "parameters:${v}";
                }
            }

            $out = implode(', ', $out);
            $out = '{' . $out . '}';
            return $out;
        } else {
            return false;
        }
    }
 
 
 
    /**
     * Return source type
     * 
     * Source can be a:
     *      - URL
     *      - Array
     *      - Javascript callback
     * 
     *  @param  mixed       jQueryUiAutoComplete source option
     *  @return string 
     * 
     **/
    private function __getSourceType($value) {
        if (is_array($value)) return 'DATASOURCE';
        elseif (parse_url($value) !== FALSE) return 'URL';
        else return 'CALLBACK';
    }
    
    
    /**
     * Build "source" of jquery-ui autocomplete based on
     * the value of the index source of helper options.
     * 
     * @param       mixed       Value of $jQueryUiOptionsDefaults
     * @param       array       Allowed options
     * @todo        Test callback mode please
     * @return      string
     **/
    private function __buildSource($value, $field) {
        switch($this->__getSourceType($value)) {
            // Is a URL
            case'URL':                
                list($model, $field) = explode('.', $field);
                return "function (request, response) {
                    $.ajax({
                    url: '$value',
                    type: 'POST',
                    dataType:'json',
                    data:{ model: '$model', field: '$field', term: request.term },
                    beforeSend: function (jqXHR, settings) { {$this->callbacks['beforeSend']}  },
                    success: function (data) { {$this->callbacks['success']} }
                    })
                   }";
            break;
            
            // Static choices
            case 'DATASOURCE':
                return json_encode($value);
            break;
            
            // Is a javascript callback maybe
            case 'CALLBACK':
                return "function (request, response) { $value }";
            break;

        }
    }
    
   /**
    *   Check if $field match format ModelName.ModelField
    * 
    *   @param      string  $field  Field
    *   @return     bool
    *   @access     private
    */ 
    public function checkFieldFormat($field) {
        preg_match('/(?P<model>[a-z0-9]+)\.(?P<field>[a-z0-9]+)/i', $field, $tmp);

        if ($field == null || sizeof($tmp) < 2) {
            trigger_error(__d('AutoCompleteHelper', 'Please give me a field in format: Model.field', true));
            return false;
        } else return true;
    }
        
    /**
     * Check if the option has default value or not.
     * 
     *  @param      string      option name
     *  @param      string      option value 
     *  @return     boolean
     *  @access     private
     * 
     * @version     0.1    
     * 
     */
    private function __hasDefaultValue($option, $value) {
        if (!array_key_exists($this->jQueryUiOptions)) 
            trigger_error("$option should be a valid jquery-ui autocomplete options.");
        
        return strncasecmp($value, $this->jQueryUiOptions[$option]) === 0;
    }


   /**
    *   Use auto-complete helper in "whitelist" mode (add $allowFields on json)
    * 
    *   @param  string  $field          Model and field name (in Model.field format)
    *   @param  array   $allowedFields  Fields name to append to json result (only fields name).
    *   @return void
    */ 
    private function __createWhiteList($field, $allowedFields) {
        $this->__appendRuleToSession("{$field}", $allowedFields);
        
    }

    function __hash($page) {
        return base64_encode($page);
    }
        
   /**
    *   Create new Session index only for AutoCompleteHelper
    * 
    *   @param  string  $key    Session key
    *   @param  string  $value  Session value
    *   @return bool
    */ 
    private function __appendRuleToSession($key, $value) {
        $field = Inflector::camelize( str_replace('.', '_', $key) );
        $page = $this->__hash( Router::url('') );
        $this->session->write("AutoComplete.{$page}.{$field}", $value);
    }
    
    
}
