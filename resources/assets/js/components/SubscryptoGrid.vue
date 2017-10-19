<template>
    <div class="container">
        <div class="row">
            <div class="col-md-offset-9 col-md-3" style="margin-bottom: 1em;">
                <form id="search">
                    Search <input name="query" v-model="searchQuery" v-on:keyup="queryHandler">
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table>
                    <col v-for="w in widths" :width="w" />
                    <thead>
                        <tr>
                            <th v-for="key in columns"
                                @click="sortBy(key)"
                                :class="{ active: sortKey == key }"
                                v-show='! hidden.includes(key)'>
                                {{ key | capitalize }}
                                <span class="arrow" :class="sortOrders[key] > 0 ? 'asc' : 'dsc'">
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="entry in filteredData">
                            <td v-for="key in columns" v-show='! hidden.includes(key)'>{{entry[key]}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                Page <em>{{ selected }}</em> of <em>{{ totalPages }}</em>
            </div>
            <div class="col-md-6 col-md-offset-3">
                <ul class="paginator pull-right">
                    <li :class="{ hidden: firstPageSelected }">
                        <a @click="prevPage()" @keyup.enter="prevPage()" tabindex="0"><slot name="prevContent">{{ prevText }}</slot></a>
                    </li>
                    <li v-for="page in pages">
                        <a @click="filterHandler(page)" @keyup.enter="handlePageSelected(page)" tabindex="0">{{ page }}</a>
                    </li>
                    <li :class="{ hidden: lastPageSelected }">
                        <a @click="nextPage()" @keyup.enter="nextPage()" tabindex="0"><slot name="nextContent">{{ nextText }}</slot></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
module.exports = {
    props: {
        columns: Array,
        widths: Array,
        hidden: Array,
        filterable: Array,
        filterKey: String,
        endPoint: String,
        limit: Number,
        pageList: Number,
        pageRange: {
            type: Number,
            default: 3
        },
        marginPage: {
            type: Number,
            default: 1
        },
        initialPage: {
            type: Number,
            default: 0
        },
        prevText: {
            type: String,
            default: '<<'
        },
        nextText: {
            type: String,
            default: '>>'
        }
    },
    data: function () {
        var sortOrders = {}
        this.columns.forEach(function (key) {
            sortOrders[key] = 1
        })
        return {
            total: 0,
            sortKey: '',
            searchQuery: '',
            sortOrders: sortOrders,
            data: [],
            selected: 1,
            orderBy: 'id'
        }
    },
    computed: {
        firstPageSelected() {
            return (this.selected === 1)
        },
        lastPageSelected() {
            return (this.selected === this.pageCount - 1) || (this.pageCount === 0)
        },
        totalPages: function() {
            let pages = Math.floor(this.total / this.limit);
            if(this.total % this.limit)
                pages++;
            return pages;
        },
        filteredData: function () {
            var sortKey = this.sortKey
            var filterKey = this.searchQuery && this.searchQuery.toLowerCase()
            var order = this.sortOrders[sortKey] || 1
            var data = this.data
            if(data.length < this.limit)
            {
                for(let i = data.length - 1; i < this.limit; i++)
                {
                    let spacer = {};
                    // spacer[this.columns[1]] = "&nbsp;"
                    data.push(spacer)
                }
            }
            return data
        },
        pages: function () {
            
            if(this.selected < this.pageList) 
            {
                let p = [...Array(this.pageList + 1)].map((v, i) => i ); //Array(this.pageList + 1);
                p.shift();
                console.log(p);
                return p; // [...Array(this.pageList + 1).keys()];
            } else { 
                let p = [...Array(this.pageList + 1)].map((v, i) => this.selected - this.pageList + i + 1);
                p.shift();
                console.log(p);
                return p;
            }
            // let items = [];
            // let selected = this.selected;
            // let half = (this.pageList - 1) / 2; 
            
            // for(let i = selected - half; i <= selected + half; i++)
            // {
            //     items.push(i);
            // }
            console.log(items)
            return items;
        }
    },
    filters: {
        capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        }
    },
    methods: {
        sortBy: function (key) {
            this.sortKey = key
            this.sortOrders[key] = this.sortOrders[key] * -1
            this.orderBy = key
            this.filterHandler(this.selected)
        },
        filterHandler: function(selected) {
            this.selected = selected

            axios.post(this.endPoint, { filter: this.searchQuery, 
                                        filterable: this.filterable,
                                        limit: this.limit,
                                        orderBy: this.orderBy,
                                        sortOrder: this.sortOrders[this.orderBy],
                                        offset: selected - 1
            })
            .then(response => {
                this.data = response.data.results
                this.total = response.data.total
            })
            .catch(e => {
                this.errors.push(e)
            }) 
        },
        queryHandler(event) {
            this.filterHandler(1);
        },
        handlePageSelected(selected) {
            if (this.selected === selected) return
            this.selected = selected
            // this.clickHandler(this.selected)
            this.filterHandler(this.selected)
        },
        prevPage() {
            if (this.selected <= 0) return
            this.selected--
            this.filterHandler(this.selected)
        },
        nextPage() {
            if (this.selected >= this.pageCount - 1) return
            this.selected++
            this.filterHandler(this.selected)
        }
    },
    created: function(){

        axios.post(this.endPoint, { limit: this.limit,
                                    orderBy: this.orderBy })
        .then(response => {
            this.data = response.data.results
            this.total = response.data.total
        })
        .catch(e => {
            this.errors.push(e)
        })
    }
}
</script>
<style scoped>
body {
  font-family: Helvetica Neue, Arial, sans-serif;
  font-size: 14px;
  color: #444;
}

.paginator {
    font-size: 1.5em;
}
a {
  cursor: pointer;
}

table {
  /*border: 2px solid #42b983;*/
  border: 2px solid #99c1bf;
  border-radius: 3px;
  background-color: #fff;
  table-layout: fixed;
  border-spacing: unset;
  empty-cells: show;
  width: 100%;
}

th {
  /*background-color: #42b983;*/
  background-color: #99c1bf;
  color: #fff;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  text-align: center;
}

/*td {
  background-color: #f9f9f9;
}*/
tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}

th, td {
  min-width: 120px;
  padding: 10px 20px;
}

th.active {
  color: #fff;
}

th.active .arrow {
  opacity: 1;
}

td:empty {
  height: 2.75em;
}

li {
    display: inline-block;
    width: 1.2em;
    margin: 4px;
    zoom: 1;
}

.arrow {
  display: inline-block;
  vertical-align: middle;
  width: 0;
  height: 0;
  margin-left: 5px;
  opacity: 0.66;
}

.arrow.asc {
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  border-bottom: 4px solid #fff;
}

.arrow.dsc {
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  border-top: 4px solid #fff;
}
</style>