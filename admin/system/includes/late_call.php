<?php 
//------------------------------------------------------------------------------------------------------------------
//INFO:  TÜM SAYFALAR YÜKLENDİKTEN SONRA VE OUTPUT İŞLEMİNDEN ÖNCE ÇALIŞTIRILMASI GEREKEN KODLAR BURADA TANIMLANIYOR
//------------------------------------------------------------------------------------------------------------------



// dataGrid içinde tanımlanan sıralama işleminin gerçekleşmesi için çağırılacak fonksiyon burada belirlenip işletiliyor.
if(in_admin && ($_POST["admin_action"] == "sortDataGrid"))
{
	/*
	 * Önemli Not: Birgün sortable işlemi yapan mesela dataGrid gibi bir özelliği admin dışında kullanmaya karar verirsen
	 * burayı mutlaka kontrol et, admin dışında çalıştırırsan çok büyük bir güvenlik açığı olur. şu anda mecburiyetten php 
	 * tarafında sort işlemini yapan fonksiyon post ile gönderilen değişken ile belirleniyor. Bu da admin dışında kullanıldığında
	 * çok büyük açık olur.
	 */
	
	$sortEvent = $_POST["event"];
	$fixed_array = array();
	$orderList = $_POST["order"];

	foreach ($orderList as $val=>$key)
	{
		$fixed_array[] = (object) array("key"=>$key,"order"=>$val);
	}

	if($sortEvent($fixed_array) === false)
		echo json_encode(array("error"=>true));
	else
		echo json_encode(array("error"=>false));

	exit;
}