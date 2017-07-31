<?php
class swoole_task{
	public static function attachadd($data){
		$attachment=new AttachmentModel("attachment");
    $attachment->AttCreate($data);
	}
	
}
 