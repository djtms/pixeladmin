function uniqid(calc_server_side, prefix, more_entropy)
{
	// farklı bilgisayarlardan içerik girilmeye çalışıldığında her bilgisayarın saati kendine göre olacağı için
	// uniqid yi javascript tarafından hesaplamak çok sağlıklı olmayabilir, başka bir bilgisayarla üretilen uniqueid değeri
	// daha önce başka yerde (mesela i18n olarak) kullanılan bir değişken olabilir ve çakışma yaşanabilir. bu olasılığı düşürmek
	// için default olarak uniqid değerini server tarafından alıyoruz. ama istenirse "calc_server_side" parametresi "true" yapılarak
	// javascript tarafından üretilen kod kullanılabilir. Ayrıca server tarafından değer alınırken hata oluşursa işlemler yarım kalmasın diye
	// javascript tarafından unique id değeri üretilir.
	
	
	if((typeof(calc_server_side) == 'undefined') || (calc_server_side === true))
	{
		var id;
		prefix = prefix !== undefined ? "&prefix=" + prefix : "";
		more_entropy = more_entropy !== undefined ? "&more_entropy=" + more_entropy : "";
		
		$.ajax({
			type:"post",
			data:"admin_action=getUniqueId" + prefix + more_entropy,
			dataType:"json",
			async:false,
			success:function(response){
				id = response.uniqid;
			},
			error:function(){
				id = uniqid_client_side(prefix, more_entropy) + Math.floor(Math.random() + 1000);
			}
		});
		
		return id;
	}
	else
	{
		return uniqid_client_side(prefix, more_entropy);
	}
}


function uniqid_client_side (prefix, more_entropy) {
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kankrelune (http://www.webfaktory.info/)
    // %        note 1: Uses an internal counter (in php_js global) to avoid collision
    // *     example 1: uniqid();
    // *     returns 1: 'a30285b160c14'
    // *     example 2: uniqid('foo');
    // *     returns 2: 'fooa30285b1cd361'
    // *     example 3: uniqid('bar', true);
    // *     returns 3: 'bara20285b23dfd1.31879087'
    if (typeof prefix == 'undefined') {
        prefix = "";
    }

    var retId;
    var formatSeed = function (seed, reqWidth) {
        seed = parseInt(seed, 10).toString(16); // to hex str
        if (reqWidth < seed.length) { // so long we split
            return seed.slice(seed.length - reqWidth);
        }
        if (reqWidth > seed.length) { // so short we pad
            return Array(1 + (reqWidth - seed.length)).join('0') + seed;
        }
        return seed;
    };

    // BEGIN REDUNDANT
    if (!this.php_js) {
        this.php_js = {};
    }
    // END REDUNDANT
    if (!this.php_js.uniqidSeed) { // init seed with big random int
        this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
    }
    this.php_js.uniqidSeed++;

    retId = prefix; // start with prefix, add current milliseconds hex string
    retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
    retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
    if (more_entropy) {
        // for more entropy we add a float lower to 10
        retId += (Math.random() * 10).toFixed(8).toString();
    }

    return retId;
}