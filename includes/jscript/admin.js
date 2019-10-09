function confirm_del(msg,url){
	if (confirm(msg))
		$.get(url, {});
}
//, refresh
function checked_items(formname,chkname){
  form=document.forms[formname];
  for (i=0; i<=form.elements.length-1; i++)
   if (form.elements[i].name.indexOf(chkname,0)>=0)
      form.elements[i].checked=form.allchecked.checked;	
}

function select_action(action, url){
	document.getElementById('action').value=action;
	
	if (action == 'deleting')
		if (confirm('Вы действительно хотите удалить выделенные продукты?'))
			document.getElementById('actionform').submit();

	if (action == 'disable')
		if (confirm('Вы действительно хотите отключить выделенные категории и продукты?'))
			document.getElementById('actionform').submit();

	if (action == 'enable')
		if (confirm('Вы действительно хотите включить выделенные категории и продукты?'))
			document.getElementById('actionform').submit();
			
	if (action == 'moving')
		$('#categories').load(url);
}

function moving(){
    if (confirm('Переместить выбранные позиции ? '))
	document.getElementById('actionform').submit();	
}

function setSelectedIndex(selectId, value){
	var sel = document.getElementById(selectId);

	for (i=0; i<sel.options.length; i++)
	{
		if (sel.options[i].value == value)
			sel.selectedIndex = i;
	}
}

function openWindow(url, w, h){
	open(url, "reportWindow", "status=no, toolbar=no, menubar=yes, scrollbars=yes, width="+w+", height="+h);
}

function getList(id, url){
	$("#"+id).load(url);
}

function categorySwitchText(value){
	if (value == 0)
		$('#text').css('display', 'none');
	else	
		$('#text').css('display', 'block');
}

// вывод формы для привязки статей к стране
function countryShowCategorys(url){
	$('#editForm').load(url);
	$('#editForm').css({'display':'block', 'top':330, 'left':250});
}

// клик по стране для выбора школ для категории
function selectSchool(url, id){
	$('#school').load(url+id);
	$('.country').removeClass('country_selected');
	$('#'+id).addClass('country_selected');
}

//добавление позиции справочника
function addItem(){
	var url = 'http://'+window.location.hostname+'/ajax.php?module=Dictionary&method=addItem&table='+$('#table').val()+'&name='+encodeURIComponent($('#new').val());
	$('#list').load(url);
}

//  улаоееие позиции справочника
function delItem(id){
	if (!confirm('Удалить?'))
		return;
	var url = 'http://'+window.location.hostname+'/ajax.php?module=Dictionary&method=delItem&table='+$('#table').val()+'&id='+id;
	$('#list').load(url);
}

// обновление позиции справочника
function updateItem(id){
	var url = 'http://'+window.location.hostname+'/ajax.php?module=Dictionary&method=updateItem&table='+$('#table').val()+'&id='+id+'&name='+encodeURIComponent($('#'+id).val());
	$('#list').load(url);
}

//добавление елемента в категории
function addSchoolItem(name, item){
	var id = $('#'+item+' option:selected').val();
	
	if (id == 0 || element('del_'+name+'-'+id)) return;
		
	var ul = element(name);

	ul.innerHTML +='<li><input type="hidden" name="'+name+'-'+id+'" value="'+id+'" />'
	+'<input type="checkbox" id="'+name+'_del-'+id+'" name="'+name+'_del-'+id+'"> '+$('#'+item+' option:selected').text()+"</li>";
}

function del_binded_articles(data){
	if (confirm('Удалить связь со статьей?'))
		$('#binded_articles').load(data);
}