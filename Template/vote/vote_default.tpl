<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{$vote['title']}</title>
  <css href="__PUBLIC__/bootstrap3/css/bootstrap.min.css" />
  <css href="__PUBLIC__/lib/jquery-ui-1.10.4/css/no-theme/jquery-ui.css" />
  <css href="__PUBLIC__/home/default/style.css" />
  <js href="__PUBLIC__/jquery-1.12.js" />
  <js href="__PUBLIC__/lib/jquery-ui-1.10.4/js/jquery-ui.js" />
  <js href="__PUBLIC__/lib/notify.min.js" />
  <js href="__PUBLIC__/bootstrap3/js/bootstrap.min.js" />
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <h2>{$vote['title']}</h2>
        <p>投票说明：{$vote['summary']}</p>
        <p>投票帮助：{$vote['help']}</p>
      </div>
    </div>
    <div class="row">
      <form action="javascript:void(0)" id="vote-form">
      <volist name="vote_items_dataset" id="vote_item">
        <div class="col-md-2 col-sm-3">
          <div class="vote-item" style="position:relative;padding-top:100%">
            <label>
              <div class="thumb" style="position:absolute;top:0;"><img src="{:C('UPLOAD_BASE')}{$vote_item['thumb']}" class="img-responsive" alt="{$vote_item['title']}"></div>
              <div class="title">{$vote_item['title']} <input type="checkbox" name="vote_item_unids[]" value="{$vote_item['unid']}"></div>
            </label>
          </div>
        </div>
      </volist>
      <input type="hidden" name="unid" value="{$vote['unid']}">
      <if condition="$vote['verify_type'] eq 1">
        <include file="Partial/mobile_verify" />
      </if>
      <div class="col-md-12 text-center">
        <input type="submit" value="投票" id="vote-submit-btn" class="btn btn-primary">
      </div>
      </form>
    </div>
  </div>
</body>
<script>
  (function($){
    var submitUrl = "{:U('Vote/submit')}";

    $('#vote-submit-btn').on('click', function(){
      var $this = $(this);

      if ($this.attr('disabled')) {
        return;
      }

      $this.attr('disabled', 'disabled');

      var data = $('form#vote-form').serializeArray();
      
      $.ajax({
        url: submitUrl,
        data: data,
        type: 'POST',
        success: function(r) {
          if (r.status == 'ok') {
            alert('投票完成');
          } else {
            alert(r.msg);
          }
          $this.removeAttr();
        },
        error: function() {
          $this.removeAttr();
        },
        dataType: 'json'
      })
    });

  })(jQuery);
</script>
</html>