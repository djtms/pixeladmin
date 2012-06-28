$(EditRoleStart);

function EditRoleStart()
{
	 $('.sortableTreeList ul').nestedSortable({
         handle: 'span',
         items: 'li',
         toleranceElement: '> span',
         listType: "ul",
         placeholder: "placeholder",
         forcePlaceholderSize: true
     });
}