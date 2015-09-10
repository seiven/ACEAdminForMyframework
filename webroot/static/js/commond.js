var s = {
	alert : function($message) {
		// 打开提示dialog
		bootbox.alert($message);
	},
	del : function(o) {
		// 绑定删除事件
		var $url = o.data('url');
		if ($url == '' || $url == undefined)
			return;
		var $message = o.data('message') || '您确定要删除这条记录吗?';
		bootbox.confirm($message, function(result) {
			if (result == true) {
				$.getJSON($url, {
					isajax : 1
				}, function(json) {
					if (json.status == true) {
						// 删除成功,删除同一行元素
						location.reload();
					} else {
						// 执行失败,提示信息
						bootbox.alert(json.message);
					}
				});
			}
		});
	},
	dialog : function(o) {
		var $type = o.data('type') || 'view';// 类型 form/view
		var $title = o.data('title') || '提示';
		var $url = o.data('url') || false;
		var $backfrop = o.data('url') || true;
		var $closeButton = o.data('closeButton') || true;
		var $message = o.data('message') || '没有设置内容';
		var $form = o.data('form') || 'bootbox_form';

		if ($message.substr(0, 1) == '#') {
			// 已存在的表单dom
			$message = $($message).html();
		} else if ($url != false) {
			// 从URL ajax获取
			$.ajax({
				type : "get",
				url : $url,
				data : {},
				async : false,
				success : function(data) {
					$message = data
					// 是否有form
					var _ = $(data);
					if ($(_).find('form').length > 0) {
						// 只支持一个
						$form = $(_).find('form').attr('id');
					}
				}
			});
		}
		bootbox.dialog({
			// dialog的内容
			message : $message,
			// dialog的标题
			title : $title,
			// 退出dialog时的回调函数，包括用户使用ESC键及点击关闭
			onEscape : function() {
			},
			// 是否显示此dialog，默认true
			show : true,
			// 是否显示body的遮罩，默认true
			backdrop : $backfrop,
			// 是否显示关闭按钮，默认true
			closeButton : $closeButton,
			// 是否动画弹出dialog，IE10以下版本不支持
			animate : true,
			// dialog的类名
			className : "my-modal",
			// dialog底端按钮配置
			buttons : {
				// 其中一个按钮配置
				success : {
					// 按钮显示的名称
					label : "确定",
					// 按钮的类名
					className : "btn-success",
					// 点击按钮时的回调函数
					callback : function() {
						if ($type == 'form')
							s.bootbox_form($form);

						if ($type == 'view')
							s.bootbox_view();
					}
				}
			}
		});
	},
	bootbox_form : function($form) {
		var form = $("#" + $form);
		if (form.length < 1)
			return;
		var params = form.serialize();
		$.ajax({
			type : "post",
			dataType : "json",
			url : form.attr('action'),
			data : params + '&isajax=1',
			success : function(json) {
				if (json.status == true) {
					// 添加成功
					if(json.message == null){
						if (json.redirect_url) {
							top.window.location.href = json.redirect_url;
						} else {
							bootbox.hideAll();
							location.reload();
						}
					}else{
						bootbox.alert(json.message, function(result) {
							if (json.redirect_url) {
								top.window.location.href = json.redirect_url;
							} else {
								bootbox.hideAll();
								location.reload();
							}
						});
					}
				} else {
					// 失败
					bootbox.alert(json.message);
				}
			},
			error : function() {
				bootbox.alert("数据提交错误");
			}
		});
	},
	bootbox_view : function() {

	},
	menu_selected : function(c, a) {
		var $on_uri = '#' + c + '_' + a;
		// $on_uri = "#<{$controller}>_<{$action}>";
		$('.nav-list li').removeClass('active');
		$($on_uri).addClass('active');
		$($on_uri).parent().parent().addClass('active');
		$($on_uri).parent().parent().addClass('open');
	},
	submit : function(f) {
		var $url = f.attr('action');
		var $method = f.attr('method') || 'POST';
		if ($url == '' || $url == undefined) {
			bootbox.alert('表单没有提交地址!');
			return false;
		}
		var params = f.serialize();
		$.ajax({
			type : $method,
			url : $url,
			data : params + '&isajax=1',
			async : false,
			dataType : 'JSON',
			success : function(json) {
				if (json.status == true) {
					// 添加成功
					bootbox.alert(json.message, function(result) {
						if (json.redirect_url) {
							top.window.location.href = json.redirect_url;
						} else {
							top.window.location.href = '/';
						}
					});
				} else {
					// 失败
					bootbox.alert(json.message);
				}
			}
		});
		return false;
	}
}

$(function() {
	// 删除
	$('.delete').on(ace.click_event, function() {
		// 发起ajax请求
		s.del($(this));
	})
	// 打开dialog
	$('.open_dialog').on(ace.click_event, function() {
		s.dialog($(this));
	});
	// 提醒
	$('.form_ajax').submit(function() {
		return s.submit($(this));
	})
	// 确认

})
