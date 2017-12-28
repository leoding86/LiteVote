<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{$vote['title']}</title>
</head>
<body>
    <div>投票标题：{$vote['title']}</div>
    <div>投票说明：{$vote['summary']}</div>
    <div>投票帮助：{$vote['help']}</div>
    <div>开始时间：{$vote['start_time']->format('Y-m-d H:i:s')}</div>
    <div>结束时间：{$vote['end_time']->format('Y-m-d H:i:s')}</div>
    <div>投票间隔：{$vote['interval']} [1：仅一次；2：每日一次]</div>
    <div>选择上限：{$vote['select_max_limits']}</div>
    <div>选择下限：{$vote['select_min_limits']}</div>
    <div>验证方式：{$vote['verify_type']} [1：手机；2：微信]</div>
    
    <div>
        <form action="__SELF__" method="POST">
        <volist name="vote_items_dataset" id="vote_item">
            <div>投票项目标题：{$vote_item['title']}</div>
            <div>投票图片链接：{:C('UPLOAD_BASE')}{$vote_item['thumb']}</div>
            <div>投票项目unid：{$vote_item['unid']}</div>
            <div>勾选框：<input type="checkbox" name="vote_item_unids[]" value="{$vote_item['unid']}"></div>
        </volist>
            <div>投票unid：<input type="hidden" name="unid" value="{$vote['unid']}"></div>

            <div>如果是手机验证，添加手机验证模板</div>
            <if condition="$vote['verify_type'] eq 1">
                <include file="Partial/mobile_verify" />
            </if>

            <button type="submit">提交</button>
        </form>
    </div>
</body>
</html>