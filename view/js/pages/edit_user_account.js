$(EditUserAccountStart);

function EditUserAccountStart()
{
	user_role_count = admin_user_roles_list.length;

	for(var i=0; i<user_role_count; i++)
	{
		$("[name='user_roles[]'][value='" + admin_user_roles_list[i].role_id + "']").attr("checked", true);
	}
}