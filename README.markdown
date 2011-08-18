# CakePHP Jquery-ui autocomplete
A easy way for use auto-complete on CakePHP


## Requirements

* jQuery
* jQuery-ui
* jquery.json.min.js (http://code.google.com/p/jquery-json/) 

## Installation
* Go to your CakePHP app dir (app/)
* Clone this repository:
  *  _normal mode_  
     `git clone git@github.com:hiryu85/cakephp_jqueryui_autocomplete.git app/plugins/auto_complete`
  * _as submodule_  
    `git submodule add git@github.com:hiryu85/cakephp_jqueryui_autocomplete.git app/plugins/auto_complete`     
* Add jQuery.Json.js to `auto_complete/webroot/js`
*  Include AutoComplete helper into your controller 
   * `var $helpers = array('AutoComplete.AutoComplete')`  
   * `$this->helpers[] = 'AutoComplete.AutoComplete'` 
*  Use it with $this->AutoComplete('Model.field') into your view's (*Model.field sintax is required*)
*  All done :)


## AutoComplete methods:
*You can found FormHelperOptions at http://api.cakephp.org/class/form-helper
and JQueryUiAutoCompleteOptions at http://jqueryui.com/demos/autocomplete/*


 * ## input($modelNameAndField, $HtmlHelperOptions, $JQueryUiAutoCompleteOptions)
  *Render an input element with auto-complete.*
  
    <?php echo 
            $this->Form->create('Event').
            $this->AutoComplete->input('Event.city', array('placeholder' => 'Rome'), array('delay' => 600)).
            $this->Form->input('Event.date').
            $this->Form->end(__('Search event'))    
    ?>
 

 * ## multiple($modelNameAndField, $HtmlHelperOptions, $JQueryUiAutoCompleteOptions)
  *Render an input element with multiple auto-complete.*
  
    <?php echo
            $this->Form->create('Event').
            $this->AutoComplete->multiple('Event.city', array('placeholder' => 'Rome'), array('delay' => 600)).
            $this->Form->input('Event.date').
            $this->Form->end(__('Search event'))    
    ?>


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

