require.config({
    paths: {
        'async': '../addons/cwmap/js/async',
        'BMap3': ['//api.map.baidu.com/api?v=3.0&ak=mXijumfojHnAaN2VxpBGoqHM'],
    },
    shim: {
        'BMap3': {
            deps: ['jquery'],
            exports: 'BMap3'
        }
    }
});
