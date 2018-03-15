<div class="panel-body">
    <form class="form-horizontal" method="POST" action="{{ url('admin/order-export') }}">
        {{ csrf_field() }}
        
        <div class="form-group">
            <label class="col-md-2 control-label">下单日期</label>

            <div class="col-md-10 form-inline">
                <input type="text" class="form-control" name="date_start" required placeholder="YYYY-MM-DD"> ~ <input type="text" class="form-control" name="date_end" required placeholder="YYYY-MM-DD">
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-12 col-md-offset-2">
                <button type="submit" class="btn btn-primary" onclick="doExport(event)">导出</button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
	function doExport(e) {
		e.preventDefault();
     	$.ajax({
			url: "{{ url('admin/order-export') }}",  
			type: 'POST',
			data: $('form').serialize(),
			success: function (res) {
				if(res.error){
					alert(res.msg);
				}

				if(res.succ){
					location.href = res.redirect;
				}
			}
		});  
	} 
</script>