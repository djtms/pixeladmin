$(FilesStart);

function FilesStart()
{
	
}

var FILES =  new function() {
	this.getFileInfoById = function(file_id){
		var file = false;
		$.ajax({
			type:"post",
			data:"admin_action=getFileInfoById&file=" + file_id,
			dataType:"json",
			async:false,
			success:function(response){
				file = response;
			}
		});
		
		return file;
	};
};




