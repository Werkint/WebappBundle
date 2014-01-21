void function (glob, doc) {
    "use strict";

    if (!glob.CONST) {
        glob.CONST = {};
    }
    var c = glob.CONST, head = doc.head;

    c._imports = new (function () {
        var list = [], loaded = 0;

        var createScript = function (row) {
            var el = doc.createElement('script');
            el.src = row[0];
            if (row[1]) {
                el.setAttribute('data-hash', row[1]);
            }
            if (row[2]) {
                el.setAttribute('class', row[2]);
            }
            el.async = true;
            return el;
        };

        this.add = function (url, hash, cls) {
            list.push([url, hash, cls]);
            if (loaded < list.length) {
                this.run();
            }
        };

        var loading = false;
        this.run = function () {
            if (loading || list.length <= loaded) {
                return false;
            }
            loading = true;

            var script = createScript(list[loaded]),
                obj = this;
            script.onload = script.onreadystatechange = function () {
                if (loading && (!this.readyState ||
                    this.readyState === 'loaded' || this.readyState === 'complete')) {
                    loading = false;

                    loaded++;
                    obj.run();

                    script.onload = script.onreadystatechange = null;
                    if (head && script.parentNode) {
                        //head.removeChild(script);
                    }
                }
            };
            head.appendChild(script);
            return true;
        };
    })();
}(window, document);