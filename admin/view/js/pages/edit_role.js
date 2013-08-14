$(EditRoleStart);

function EditRoleStart()
{
	user_permission_count = user_permissions.length;

	for(var i=0; i<user_permission_count; i++)
	{
		$("[name='permission_checked_" + user_permissions[i].permission_key + "']").attr("checked", true);
	}
	
	$("#permissionsList [type='checkbox']").click(function(){
		if($(this).is(":checked"))
			$(this).parent().parent().find("[type='checkbox']").attr("checked", true);
		else
			$(this).parent().parent().find("[type='checkbox']").attr("checked", false);
	});
}