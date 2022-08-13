const { Autocomplete } = require('../../../scripts/ui/vendor/autocomplete');

const citySearch = document.querySelectorAll('.js-citySearch');

if(citySearch.length) {

    console.log(true);

    (async () => {
        const [L, Autocomplete, init] = await Promise.all([
            import(/* webpackChunkName: "leaflet" */'leaflet').then(module => module.default),
            import(/* webpackChunkName: "autocomplete" */'../../../scripts/ui/vendor/autocomplete').then(module => module.Autocomplete),
            import(/* webpackChunkName: "citySearch"*/'./init.js').then(module => module.init)
        ]);

        citySearch.forEach(el => {
            init(Autocomplete, L, el);
        })

    })();

    // Another approach would be to use a Promise based syntax. But async/await is preferable.

    // Promise.all([
    //     import(/* webpackChunkName: "leaflet" */'leaflet'),
    //     import(/* webpackChunkName: "autocomplete" */'../../../scripts/ui/modules/autocomplete'),
    //     import(/* webpackChunkName: "citySearch"*/'./init.js')
    // ]).then(([a, b, c]) => {
    //     citySearch.forEach(el => {
    //         c.init(b.Autocomplete, a.default, el)
    //     });
    // })
    
}
