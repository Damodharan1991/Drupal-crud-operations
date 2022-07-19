<?php 
namespace Drupal\my_crud\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Messenger;


class MycrudController extends ControllerBase
{
	public function Listing()
	{
		// Table Header

		$header_table = ['id'=>t('ID'),'name'=>t('Name'),'age'=>t('Age'),'opt'=>t('Operation'),'opt1'=>t('Operation'),];
		$row = [];

		$conn = Database::getConnection();

		$query = $conn->select('my_crud','m');
		$query->fields('m',['id','name','age']);
		$result = $query->execute()->fetchAll();

		foreach($result as $value)
		{
			$delete = Url::fromUserInput('/my_crud/form/delete/'.$value->id);
			$edit = Url::fromUserInput('/my_crud/form/data?id='.$value->id);

			$row[]= ['id'=>$value->id,'name'=>$value->name,'age'=>$value->age,'opt'=>Link::fromTextAndUrl('Edit',$edit)->toString(),'opt1'=>Link::fromTextAndUrl('Delete',$delete)->toString(),];
		}

		$add = Url::fromUserInput('/my_crud/form/data');

		$text = "Add User";

		$data['table'] = ['#type'=>'table','#header'=>$header_table,'#rows'=>$row,'#empty'=>t('No Record Found'),'#caption'=> Link::fromTextAndUrl($text,$add)->toString(),];
	
		$this->messenger()->addMessage('Records Listed');

		return $data;

	}
}

?>