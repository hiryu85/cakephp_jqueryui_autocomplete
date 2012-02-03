<?php
class RemoteSourcesController extends AppController {
    var $name = 'RemoteSources';
    var $components = array('Session');
      
    public function get($session = null) {
        $this->layout = 'ajax';
        $this->autoRender = false;
        
        if (empty($this->params['form'])) return;
        $model = $this->params['form']['model'];
        $field = $this->params['form']['field'];
        $term = $this->params['form']['term'];
        
        if (!isset($this->{$model})) {
            // Module not loaded.. try to import and load model
            $this->{$model} = ClassRegistry::init($model);
        }
        
        $fields[] = "$model.$field AS label";
        $fields[] = "$model.$field AS value";
        $fields[] = "{$this->{$model}->primaryKey} AS id";

        // Append fields to json result (require $session)
        if (!is_null($session)) {
            $this->Session->id($session);
            $tmp = explode('?', preg_replace('/(\/.+:.+)/i', '', Router::url( $this->referer('/') ) ));
            $url = $tmp[0];
            $configNode = base64_encode( $url );
            
            if (!$this->Session->check("AutoComplete.{$configNode}")) {
                $this->redirect('/', 400, true);            
            } else {
                $fieldConfig = Inflector::camelize($this->params['form']['model'].'_'.$this->params['form']['field']);
                $configPath = "AutoComplete.{$configNode}.{$fieldConfig}";
                if($this->Session->check($configPath)) {
                    $whitelistedFields = explode(',', $this->Session->read($configPath));
                    $fields = array_merge($fields, $whitelistedFields);
                }                
            }            

        }
        
        $conditions[] = "$model.$field LIKE '%{$term}%'";
        $group = array("$model.$field");
        $results = $this->{$model}->find('all', compact('conditions', 'fields', 'group'));
        echo json_encode(Set::extract((array) $results,"{n}.{$model}"));
    }
    
}
