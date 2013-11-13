$(PermissionsStart);

function PermissionsStart()
{
	 $('#sortablePermissionLists .itemsList').nestedSortable({
         handle: '.item',
         items: 'li',
         toleranceElement: '> .item',
         listType: "ul",
         placeholder: "placeholder",
         forcePlaceholderSize: true,
         maxLevels:0,
         stop:function(){
        	 $("#permissionsList ul:not(.itemsList)").addClass("itemsList");
         },
         update:function(){
        	 $.ajax({
        		 type:"post",
        		 url:"admin.php?page=permissions",
        		 data: "admin_action=updatePermissionsSort&sort=" +  JSON.encode($('#permissionsList .itemsList').nestedSortable("toArray")),
        		 success:function(response){

        		 }
        	 });
         }
     });
}