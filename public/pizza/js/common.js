var baseUrl='http://web.socket.com';

_post=function(url,params,onSuccess,onError){
    var onError= arguments[3] ? arguments[3] : function(err) {};
    $.ajax({
        type: "post",
        url:  baseUrl+url,
        dataType: 'json',
        data: params,
        success: function(data) {
            if(data.code==1){
                onSuccess(data.data);
            }else{
                alert(data.msg);
            }
        },
        error: function(error) {
            onError(error);
        }
    });

}
chuanzhi=function(){
    var url=location.search;
    var Request = new Object();
    if(url.indexOf("?")!=-1)
    {
    　　var str = url.substr(1)　//去掉?号
    　　strs= str.split("&");
    　　for(var i=0;i<strs.length;i++)
    　　{
    　　 　 Request[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
    　　}
        return Request;
    }
    
}