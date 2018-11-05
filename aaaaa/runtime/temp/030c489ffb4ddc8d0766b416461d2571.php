<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:72:"D:\Documents\GitHub\xhgj\public/../application/api\view\index\index.html";i:1536143937;}*/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>添加项目</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link href="/static/style/adminStyle.css" rel="stylesheet" type="text/css" />
    <script src="/static/js/jquery.min.1.8.2.js"></script>
    <script src="/static/js/layer/layer.js"></script>
    <script type="text/javascript" charset="utf-8" src="/static/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="/static/ueditor/ueditor.all.min.js"></script>


</head>
<body>
<div class="wrap">
    <div class="page-title">
        <span class="modular fl"><i></i><em>添加项目</em></span>
    </div>
    <form id="form1">
        <table class="list-style">
            <tr>
                <td style="text-align:right;width:15%;">项目名称：</td>
                <td>
                    <input type="text" class="textBox" name="goodsname"/>
                </td>
            </tr>
            <tr>
                <td style="text-align:right;width:15%;">项目标题：</td>
                <td>
                    <input type="text" class="textBox" name="titlename"/>
                </td>
            </tr>
            <tr>

                <td style="text-align:right;width:10%;">项目详情：</td>
                <td>
                    <textarea id="editor" name="editor" type="text/plain" style="width:1024px;height:500px;"></textarea>
                </td>
            </tr>
            <tr>
                <td style="text-align:right;">项目报价：</td>
                <td>
                    <input type="text" class="textBox length-short" name="price"/>
                </td>
            </tr>
        </table>
    </form>
    <br/><br/><br/>
    <input type="button" value="添加" style="margin-left: 200px ;width: 80px;height: 20px;"/>
</div>
</body>
</html>
<script>
    //    var ue = UE.getEditor('editor');
    //    var editor=document.getElementById("editor").value;
    //    console.log(editor);
    $("input[type='button']").click(function(){
        // header('Access-Control-Allow-Origin:http://client.runoob.com');
        $.ajax({
            url:"http://127.0.0.1/api/statistical/devices",
            type:"get",
            dataType:'JSON',
            // dataType: json,
            // beforeSend: function(request) {
            //     request.setRequestHeader("token", '476821c4d5dbacc133a98637a513f3d9');
            // },
            headers: {
                // 'Access-Control-Allow-Origin': '*',
                'token': "facd2bfcb0405d98dca46409f79541dc"
            },
            success:function(res){
                console.log(res);
//                 if(res=="ok"){
//                     layer.msg('添加成功', function(){
//                         location.href="<?php echo url('product_list'); ?>";
//                     });
//                 }else{
//                     layer.msg(res,{icon:2});
//                 }
            }
        });
    });
</script>