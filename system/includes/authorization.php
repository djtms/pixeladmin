<?php

if(in_admin && $ADMIN->USER->loggedInUser)
{
	// Şimdilik aktif etmiyoruz
	return;
	
	if(!$ADMIN->AUTHORIZATION->checkAuthorization())
	{
		postMessage("Bu sayfaya erişmek için yeterli yetkiniz yok!");
		// TODO: burada yönlendirmeyi dasboard a yapmak yerine statik bir html sayfası tasarlayıp
		// oraya yönlendir. Çünkü dashboard a da izin verilmek istenmeyebilir.
		header("Location:admin.php?page=dashboard");
		exit;
	}
}
