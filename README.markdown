# CakePHP Jquery-ui autocomplete
A easy way for use auto-complete on CakePHP


## Requirements

* jQuery
* jQuery-ui

## Installation
* Go to your CakePHP app dir (app/)
* Clone this repository:
  *  _normal mode_  
     `git clone git@github.com:hiryu85/cakephp_jqueryui_autocomplete.git app/plugins/auto_complete`
  * _as submodule_  
    `git submodule add git@github.com:hiryu85/cakephp_jqueryui_autocomplete.git app/plugins/auto_complete`     
*  Include AutoComplete helper into your controller with:
   * Append this helper into your controller (all actions can use it): `var $helpers = array('AutoComplete.AutoComplete')`  
   * Append this helper into your action:    (only current action load it): `$this->helpers[] = 'AutoComplete.AutoComplete'` 
*  Use it with $this->AutoComplete('Model.field') into your view's (*Model.field sintax is required*)
*  Add into your views/layouts/default.ctp `$this->Js->writeBuffer(array('onDomReady' => true))` 
*  All done :)

## About security
This helper allows you to set the data source from where do auto from three different types: RemoteSource (default, and magic), a url to your action, a static source (an array).
If you are using RemoteSource, *and if you use the version v1 of this plugin* can be unsafe (please update), because there is no special protection to limit the fields on which to make the autocomplete.
*This does not happen in version 2.*

Every time this helper call input() or multiple() methods, it make a session for with field name for prevent hacking (with curl etc.).
So, anyone can't get any private field of your model (with RemoteSources Controller) if the helper not allow autocomplete for this field.

If you use argument $AutoCompleteHelperSettings (of input and multiple methods) you can set a whitelist for
append another fields to json result.

## AutoComplete methods:
*You can found FormHelperOptions at http://api.cakephp.org/class/form-helper
and JQueryUiAutoCompleteOptions at http://jqueryui.com/demos/autocomplete/*


 * ## input($modelNameAndField, $FormHelperOptions, $JQueryUiAutoCompleteOptions, $AutoCompleteHelperSettings)
  *Render an input element with auto-complete.*
    `<?php echo 
       $this->Form->create('Event').
       $this->AutoComplete->input(
           'Event.city', 
            array(
              'placeholder' => 'Rome'
            ),
          array('delay' => 600)
       ).
       $this->Form->input('Event.date').
       $this->Form->end(__('Search event'))    
    ?>`
 

 * ## multiple($modelNameAndField, $FormHelperOptions, $JQueryUiAutoCompleteOptions, $AutoCompleteHelperSettings)
  *Render an input element with multiple auto-complete.*
     
     `<?php echo
          $this->Form->create('Event').
          $this->AutoComplete->multiple('Event.city', array('placeholder' => 'Rome'), array('delay' => 600)).
          $this->Form->input('Event.date').
          $this->Form->end(__('Search event'))    
     ?>`


# Configuration 
## AutoCompleteHelper options
*Please, read about this configuration at jquery-ui homepage*
####  Options 
 * integer $delay  ` default=300`   
 * boolean $disabled ` default=false` 
 * mixed   $source   `default="/auto_complete/RemoteSources/get"`  this is an magic url   
 * integer $minLength  ` default=4` 

#### Events
* search()
* focus()
* select(event, ui)

## How override default AutoComplete default options?
You can give to AutoComplete helper method a custom options (and events callback) with:

     <?php 
     echo $this->AutoComplete->input(
           'Event.city', 
          array(), /* FormHelper options */
           /* JqueryUiAutoComplete options */
           array(
               'delay' => 600,
               'minLength' => 4,

              /* And now, override Events callback */
             'select' => "function() { somethingElse();  }",
           )
           /* AutoCompleteHelper settings */
           array(
                'fields' => 'cap,state,uuid'    // Append some extra fields to json result
           )
     )` 



### Change source of auto-complete 
source value can be:

*   a URL resource   `/mycontroller/myaction/` 
*   an array  (static datasource)          `array('Foo', 'Bar')`
*   a javascript callback      `myCallback()`   *Not tested*

For change the source you can give to autocomplete helper the "source" key:

    $this->AutoComplete->input('Model.field', array(), array('source' => array('Foo', 'Bar') ))  
    $this->AutoComplete->input('Model.field', array(), array('source' => '/mycontroller/myspecialaction/'))
    $this->AutoComplete->input('Model.field', array(), array('source' => 'myCallback()'))

