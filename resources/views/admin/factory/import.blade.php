<div class="panel-body">
    <form class="form-horizontal" method="POST" action="{{ url('admin/factory-import') }}" enctype="multipart/form-data">
        {{ csrf_field() }}
        
        <div class="form-group">
            <div class="col-md-12">
                <input type="file" name="excel">
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary" onclick="doUpload(event)">导入</button>
                <a href="/电厂导入模板.xlsx">下载模板</a>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
	function doUpload(e) {
		e.preventDefault();
		var formData = new FormData();

		if($('input:file')[0].files.length == 0)
		{
			alert('请选择文件！');
		}
		
		formData.append('excel', $('input:file')[0].files[0]);
		formData.append('_token', $('input[name="_token"]').val());
     	$.ajax({
			url: "{{ url('admin/factory-import') }}",  
			type: 'POST',
			data: formData,
			async: false,
			cache: false,
			contentType: false,
			processData: false,
			success: function (res) {
				alert(res.msg);
				if(res.succ){
					location.reload();
				}
			}
		});  
}  
</script>