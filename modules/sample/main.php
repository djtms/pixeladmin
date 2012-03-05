<?php
/*
 * Module Name: Örnek Modül
 * Module Url: http://www.pixelturtle.com
 * Description: Basit bir modül yapısı örneği
 * Author: Mehmet Hazar Artuner
 * Author Url: http://www.hazarartuner.com
 * Version: 1.0
 * 
 * */


moduleActivationCode(__FILE__,"activationSampleModule");

function activationSampleModule()
{
	set_option("sample-module","Sample module activation has worked!");
}

addMenu("Örnek Modül","","Örnek Modül","sample-module",dirname(__FILE__) . "/sample.php",5,USER_GUEST);