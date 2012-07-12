$(EditRoleStart);

function EditRoleStart()
{
	user_permission_count = user_permissions.length;

	for(var i=0; i<user_permission_count; i++)
	{
		$("[name='permission_checked_" + user_permissions[i].permission_id + "']").attr("checked", true);
	}
}