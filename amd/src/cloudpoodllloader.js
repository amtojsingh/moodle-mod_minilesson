define(['jquery','core/log','https://cdn.jsdelivr.net/gh/justinhunt/cloudpoodll@latest/amd/build/cloudpoodll.min.js'], function($,log,CloudPoodll){
//define(['jquery','core/log','http://localhost/moodle/local/cpapi/cloudpoodll/amd/src/cloudpoodll.js'], function($,log,CloudPoodll){
    return {
        init: function(recorderclass, thecallback){
            CloudPoodll.autoCreateRecorders(recorderclass);
            CloudPoodll.theCallback = thecallback;
            CloudPoodll.initEvents();
            $( "iframe" ).on("load",function(){
                $(".mod_poodlltime_recording_cont").css('background-image','none');
            });
        }
}
});