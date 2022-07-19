<?php 
namespace Drupal\my_crud\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Messenger;
use Drupal\Core\Link;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;

class MycrudForm extends FormBase
{
	public function getFormid()
	{
		return 'mycrud_form';
	}

public function buildform(array  $form, FormStateInterface $form_state)
{

$conn = Database::getConnection();

$record = [];
if(isset($_GET['id']))
{
	$query = $conn->select('my_crud','m')->condition('id',$_GET['id'])->fields('m');
	$record = $query->execute()->fetchAssoc();
}

$form['name']=['#type'=>'textfield','#title'=>t('Name'),'#required'=>TRUE,'#default_value'=>(isset($record['name']) && $_GET['id'])? $record['name']:'',];

$form['age']=['#type'=>'textfield','#title'=>t('Age'),'#required'=>TRUE,'#default_value'=>(isset($record['age']) && $_GET['id'])? $record['age']:'',];

$country_query = $conn->select('country','c');
$country_query->fields('c',array('country_id','country_name'));
$country_query->orderBy('country_name','ASC');
$country_records = $country_query->execute()->fetchAllKeyed();
$country_options = array();

foreach($country_records as $key => $country_results)
{
	$country_options[$key]=$country_results;

}

$form['country'] =['#type'=>'select','#title'=>t('Country'),'#required'=>TRUE,'#options'=> $country_options,'#ajax'=>['callback'=>[$this,'getStates'],'event'=>'change','method'=>'html','wrapper'=>'states-to-update','progress'=>['type'=>'throbber','message'=>NULL,],],'#default_value'=>(isset($record['country_id'])&& $_GET['id'])?$record['country_id']:'',];

$states_options = [];

$form['states'] = array('#type'=>'select','#title'=>t('States'),'#required'=>TRUE, '#options'=> $states_options,'#attributes'=> array('id'=> array('states-to-update')),'#validated' => TRUE,'#default_value'=>(isset($record['state_id']) && $_GET['id']) ? $record['state_id']:'',);


$form['action']=['#type'=>'action',];

$form['action']['submit'] = ['#type' => 'submit','#value' => t('Save'),];
 

$form['action']['reset']=['#type'=>'button','#value'=>t('Reset'),'#attributes'=>['onclick'=>'this.form.reset(); return false;',],];

$link = Url::fromUserInput('/my_curd/');

$form['action']['cancel'] = ['#markup'=>Link::fromTextAndUrl(t('Back to page'),$link,['attributes'=>['class'=>'button']])->toString(),];
return $form;

}

public function validateForm(array &$form, FormStateInterface $form_state)
{
	$name = $form_state->getValue('name');

	if(preg_match('/[^A-Za-z] /', $name))
	{
		$form_state->setErrorByName('name',$this->t('Name must be in Characters Only'));

	}


$age = $form_state->getValue('age');
	if(!preg_match('/[^A-Za-z]/', $age))
	{
		$form_state->setErrorByName('age',$this->t('Age must be in Numbers Only'));

	}

parent::validateForm($form, $form_state);

}

public function submitForm(array &$form, FormStateInterface $form_state)
{
	$field = $form_state->getValues();

	$name = $field['name'];
	$age = $field['age'];
	$county_id = $field['country'];
	$state_id = $field['states'];


	if(isset($_GET['id']))
	{
		$field = ['name'=> $name,'age'=> $age,'country_id'=>$country_id,'state_id'=>$state_id];

		$query = \Drupal::database();
		$query->update('my_crud')->fields($field)->condition('id',$_GET['id'])->execute();
		$this->messenger()->addMessage('Sucessfully Updated Records ');
	}
	else
	{
		$field = ['name'=> $name,'age'=> $age,'country_id'=>$country_id,'state_id'=>$state_id];
		$query = \Drupal::database();
		$query->insert('my_crud')->fields($field)->execute();
		$this->messenger()->addMessage('Sucessfully Saved Records ');
	

	}
$form_state->setRedirect('my_crud.mycrud_controller_listing');

	}


public function getStates(array &$element, FormStateInterface $form_state)

{
	$triggeringElement = $form_state->getTriggeringElement();
	$value = $triggeringElement['#value'];
	$states = $this->getStatesByCountry($value);
	$wrapper_id = $triggeringElement["#ajax"]["wrapper"];
	$renderedField = '';

	foreach($states as $key => $value)
	{
		$renderedField .="<option value ='".$key."'>".$value."</option>";
	}
	

	$response = new AjaxResponse();
    $response->addCommand(new HtmlCommand("#".$wrapper_id, $renderedField));
    
    return $response;



	
}

public function getStatesByCountry($default_states)
{
	

	$states_record = [];
	$conn = Database::getConnection();
	$states_query = $conn->select('states','s');
	$states_query->fields('s',array('state_id','state_name',));
	$states_query->Condition('country_id', $default_states,'=');
	$states_record =  $states_query->execute()->fetchAllKeyed();
	$states_options = array();

	foreach($states_record as $key=>$states_results)
	{
		$states_options[$key]=$states_results;

	}

	

	return $states_options;
}



}







?>