const citySearch = document.querySelectorAll('.js-citySearch');

if(citySearch.length) {

    (async () => {
        const [L, Autocomplete, init] = await Promise.all([
            import('leaflet').then(module => module.default),
            import('../../../scripts/ui/vendor/autocomplete.js').then(module => module.Autocomplete),
            import('./init.js').then(module => module.init)
        ]);

        citySearch.forEach(el => {
            init(Autocomplete, L, el);
        })

    })().catch(error => console.error('City search could not be loaded.', error));

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
