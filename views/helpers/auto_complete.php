<?php
/**
 * Jquery-ui Autocomplete helpers
 * 
 * @author      Chialastri Mirko 
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
    var $helpers = array('Html', 'Form');
    /* Jquery-ui-autocomplete acceptable variables */
    var $jQueryUiOptions = array(
        'appendTo',
        'disabled',
        'delay',
        'source',
        'minLength',
    );
    /* Jquery-ui-autocomplete default values */
    var $jQueryUiOptionsDefaults = array(
        //'appendTo',
        //'disabled',
        'delay' => 300,
        'source' => 'auto_complete/RemoteSources/get',
        'minLength' => 4,
    );
    
    /**
     * AutoCompleteHelper options 
     * 
     * @todo        Implement fields option 
     */
    var $autoCompleteHelperOptions = array(
        'fields' => null,
    );
    
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
    public function input($field = null, $formHelperOptions=array(), $jQueryUiOptions=array()) {
        preg_match('/(?P<model>[a-z0-9]+)\.(?P<field>[a-z0-9]+)/i', $field, $tmp);

        if ($field == null || sizeof($tmp) < 2) {
            trigger_error(__d('AutoCompleteHelper', 'Please give me a field in format: Model.field', true));
            return;
        }
        
        // Override default jquery-ui-autocomplete options
        $jq_ui = array_merge($this->jQueryUiOptionsDefaults, $jQueryUiOptions);
        $jq_ui['source']  = $this->__buildSource($jq_ui['source'], $field);
        // Convert option to JSON
        $auto_complete_options = $this->_buildOptions($jq_ui, $this->jQueryUiOptions);
        
        $form_element = '#'.Inflector::camelize(str_replace('.', '_', $field));
        $input_source = $this->Form->input($field, $formHelperOptions);
        
        $ac = $this->Html->scriptBlock("$('$form_element').autocomplete($auto_complete_options);", array('inline' => 'true'));
        return $this->output("$input_source\n$ac");
    }
    
    
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
                    data:{model: '$model', field: '$field', term: request.term}, 
                    success: function (data) {response(data);}
                    })
                   } ";
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
    
}
