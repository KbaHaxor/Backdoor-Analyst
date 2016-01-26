$(document).ready(function(){
    
    
    function getlog()
    {
        url = window.location.href;
        if(url.indexOf("?show") > -1)
        {
            id = url.split('=')[1];
            $.get('grep.php?id='+id,function(data)
            {
                $('.load').css('display', 'none');
                $('body').html(data);
            })
        }
        else
        {
            $.get('show.php',function(data)
            {
                $('.load').css('display', 'none');
                $('body').html(data);
            })
        }
    }
    
    
    function load()
    {
        if($('.dangerous').html() == undefined)
        {
            $('.load').css('display', 'block');
        }
    }
    load();
    window.setInterval(function(){getlog();},500)
})