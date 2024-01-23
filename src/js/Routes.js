Ext.define('Tualo.routes.Braintree',{
    url: 'braintree',
    handler: {
        action: function(token){
            console.log('onAnyRoute',token);
            alert('stripe','ok');
        },
        before: function (action) {
            console.log('onBeforeToken',action);
            console.log(new Date());
            action.resume();
        }
    }
});