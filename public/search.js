/**
 * Search "Engine"
 * 2018-09-21
 * Let's do this the lazy, old-school way
 */
var response = {
    current_page: 1,
    data: [],
    from: null,
    last_page: 1,
    to: null,
    total: 0
};

var options = {
    query: "",
    type: [1,2],
    format: [0,1,2],
    sort: 1,
    by: "id",
    page: 1
};

var searching = false;

function delayPromise(duration) {
    return function(...args){
        return new Promise(function(resolve, reject){
            setTimeout(function(){
                resolve(...args);
            }, duration);
        });
    };
}

function doSearch() {
    searching = true;
    return m.request({
        method: "POST",
        url: "api/contents/search",
        withCredentials: true,
        data: options,
    }).then(function(data) {
        response = data;
        searching = false;
        m.redraw();
    }).catch(function(reason) {
        console.error("Error searching");
        searching = false;
        m.redraw();
    });
}

var suggestionAllowed = true;
var queryQueue = 0;

function search(page) {
    if(page)
        options.page = page;
    else
        options.page = 1;
    if(suggestionAllowed) {
        suggestionAllowed = false;
        doSearch().then(delayPromise(500)).then(() => {
            if(queryQueue) {
                queryQueue = 0;
                suggestionAllowed = true;
                search();
            }
        }).then(() => suggestionAllowed = true);
    } else {
        queryQueue++;
    }
}

function changeArg(arr, value, status) {
    if(status)
        if(arr.indexOf(value) !== -1)
            return;
        else
            arr.push(value);
    else
        if(arr.indexOf(value) === -1)
            return;
        else
            arr.splice(arr.indexOf(value), 1);
    search();
}

var controls = {
    view: function() {
        return [ m("div.flex.gap_s.span-full", [
            m("div.flex.wrap-no.gap-no.text_l.span-full", [
                m("input.input", {
                    type: "search",
                    name: "query",
                    id: "e-search",
                    placeholder: "Just start typing your query here",
                    onkeyup: function(e) {
                        if(options.query === e.target.value)
                            return; // Only search if value has changed
                        options.query = e.target.value;
                        search();
                    }
                })
            ]),
            m("div.flex.gap_s", [
                m("div", [
                    m("h4", "Type"),
                    m("div.span-auto", [
                        m("label.box.pa_s", [
                            m("input.checkbox", {
                                type: "checkbox",
                                checked: options.type.indexOf(1) !== -1,
                                onchange: function(e) {
                                    changeArg(options.type, 1, e.target.checked)
                                }
                            })
                        ], " Book/Novel")
                    ]),
                    m("div.span-auto", [
                        m("label.box.pa_s", [
                            m("input.checkbox", {
                                type: "checkbox",
                                checked: options.type.indexOf(2) !== -1,
                                onchange: function(e) {
                                    changeArg(options.type, 2, e.target.checked)
                                }
                            })
                        ], " Comic/Mag.")
                    ])
                ]),
                m("div", [
                    m("h4", "Format"),
                    m("div.span-auto", [
                        m("label.box.pa_s.tinted-bg-success", [
                            m("input.checkbox", {
                                type: "checkbox",
                                checked: options.format.indexOf(0) !== -1,
                                onchange: function(e) {
                                    changeArg(options.format, 0, e.target.checked)
                                }
                            })
                        ], " Reflowable")
                    ]),
                    m("div.span-auto", [
                        m("label.box.pa_s.tinted-bg-warn", [
                            m("input.checkbox", {
                                type: "checkbox",
                                checked: options.format.indexOf(1) !== -1,
                                onchange: function(e) {
                                    changeArg(options.format, 1, e.target.checked)
                                }
                            })
                        ], " Fixed Layout")
                    ]),
                    m("div.span-auto", [
                        m("label.box.pa_s.tinted-bg-danger", [
                            m("input.checkbox", {
                                type: "checkbox",
                                checked: options.format.indexOf(2) !== -1,
                                onchange: function(e) {
                                    changeArg(options.format, 2, e.target.checked)
                                }
                            })
                        ], " OMF")
                    ])
                ]),
                m("div", [
                    m("h4", "Ordering"),
                    m("div.grid-auto.gap-no", [
                        m("label.box", [
                            m("input.radio", {
                                type: "radio",
                                name: "sort",
                                value: 1,
                                checked: options.sort === 1,
                                onchange: function(e) {
                                    options.sort = 1;
                                    search();
                                }
                            })
                        ], " Field desc.\xa0↓"),
                        m("label.box", [
                            m("input.radio", {
                                type: "radio",
                                name: "sort",
                                value: 2,
                                checked: options.sort === 2,
                                onchange: function(e) {
                                    options.sort = 2;
                                    search();
                                }
                            })
                        ], " Field asc.\xa0↑")
                    ]),
                    m("div.flex.wrap-no.gap-no.collapse-t.ma-t_s", [
                        m("label.span-third.span-full-t.text-clip.box", {for: "e-text"}, "Field: "),
                        m("select.select#e-text", {
                            disabled: options.sort === 0,
                            onchange: function(e) {
                                options.by = e.target.options[e.target.selectedIndex].value;
                                search();
                            }
                        }, [
                            m("option", {value: "deliver_at"}, "Delivery"),
                            m("option", {value: "updated_at"}, "Update"),
                            m("option", {value: "name"}, "Name"),
                            m("option", {value: "id", selected: true}, "ID"),
                        ])
                    ])
                ])
            ])
            ]),
            m("span", {
            }, searching ? "Searching ⌛" : response.data.length == 0 ? "Nothing found" : response.from + "-" + response.to + " of " + response.total + " results"),
            m("article", {
                class: searching ? "loading" : ""
            }, response.data.map((item) => {
                var bg = "";
                switch (item.format) {
                    case 0:
                        bg = ".bg-success-1";
                        break;
                    case 1:
                        bg = ".bg-warn-1";
                        break;
                    case 2:
                        bg = ".bg-danger-1";
                        break;
                }
                return m("figure.r_s.pa_m.span-full" + bg, {
                    key: item.id
                }, [
                    m("div.flex.gap_m.collapse-p", [
                        m("div",  {style: "flex-basis: 15%;"}, [
                            m("div.width_xl", [
                                m("img.cover", {
                                    src: item.thumb
                                })
                            ])
                        ]),
                        m("div.flex.column.collapse-t", {style: "flex-basis: 85%;"}, [
                            m("h2", item.name),
                            m("div.tinted-prime", [
                                m("input.input.text-select-all.text_m.pa_xs", {
                                    onclick: function(e) {
                                        this.select();
                                        e.redraw = false;
                                        document.execCommand("copy");
                                    },
                                    type: "text",
                                    style: "width: 235px;",
                                    value: item.cid,
                                    readonly: true
                                })
                            ]),
                            m("table.table.bo-h.auto", [
                                m("tr", [
                                    m("td", {style: "width: 80px;"}, "Alt"),
                                    m("td", item.name)
                                ]),
                                m("tr", [
                                    m("td", {style: "width: 80px;", title: item.author.alt}, "Author"),
                                    m("td", item.author.name)
                                ]),
                                m("tr", [
                                    m("td", {style: "width: 80px;"}, "Series"),
                                    m("td", item.series.name)
                                ]),
                                m("tr", [
                                    m("td", {style: "width: 80px;"}, "Delivery Timestamp"),
                                    m("td", item.deliver_at + " (JST)")
                                ]),
                                m("tr", [
                                    m("td", {style: "width: 80px;"}, "Update Timestamp"),
                                    m("td", item.updated_at + " (JST)")
                                ]),
                                m("tr", [
                                    m("td", {style: "width: 80px;"}, "Description"),
                                    m("td", item.description2)
                                ]),

                            ])
                        ])
                    ])
                ])
            })),
            m("nav.flex.ma-t_m.gap-no.read_l.span-full", {
                style: response.total > 0 ? "font-family: Arial !important;" : "display: none;"
            }, [
                m("a.button.text-center", {
                    title: "First page",
                    onclick: function(e) {
                        search(1);
                        window.scrollTo(0,0);
                    }
                }, "⇚"),
                m("a.button.text-center", {
                    title: "Previous page",
                    onclick: function(e) {
                        search(response.current_page === 1 ? 1 : response.current_page - 1);
                        window.scrollTo(0,0);
                    }
                }, "⇐"),
                m("span.box.text-center", "Page " + response.current_page + "/" + response.last_page),
                m("a.button.text-center", {
                    title: "Next page",
                    onclick: function(e) {
                        search(response.current_page === response.last_page ? response.last_page : response.current_page + 1);
                        window.scrollTo(0,0);
                    }
                }, "⇒"),
                m("a.button.text-center", {
                    title: "Last page",
                    onclick: function(e) {
                        search(response.last_page);
                        window.scrollTo(0,0);
                    }
                }, "⇛"),
            ])
        ]}
}

function start() {
    search();
    m.mount(document.getElementById("root"), controls);
}