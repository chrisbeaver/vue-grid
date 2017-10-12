<template>
    <div class="container">
        <div class="row">
            <form id="search">
                Search <input name="query" v-model="searchQuery" v-on:keyup="filterHandler">
            </form>
        </div>
        <div class="row">
            <table>
                <thead>
                    <tr>
                        <th v-for="key in columns"
                            @click="sortBy(key)"
                            :class="{ active: sortKey == key }">
                            {{ key | capitalize }}
                            <span class="arrow" :class="sortOrders[key] > 0 ? 'asc' : 'dsc'">
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="entry in filteredData">
                        <td v-for="key in columns">
                            {{entry[key]}}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row">
            <ul class="paginator" v-if="!noLiSurround">
                <li :class="[prevClass, { disabled: firstPageSelected() }]">
                    <a @click="prevPage()" @keyup.enter="prevPage()" :class="prevLinkClass" tabindex="0"><slot name="prevContent">{{ prevText }}</slot></a>
                </li>
                <li v-for="page in pages" :class="[pageClass, page.selected ? activeClass : '', { disabled: page.disabled } ]">
                    <a v-if="page.disabled" :class="pageLinkClass" tabindex="0">{{ page.content }}</a>
                    <a v-else @click="handlePageSelected(page.index)" @keyup.enter="handlePageSelected(page.index)" :class="pageLinkClass" tabindex="0">{{ page.content }}</a>
                </li>
                <li :class="[nextClass, { disabled: lastPageSelected() }]">
                    <a @click="nextPage()" @keyup.enter="nextPage()" :class="nextLinkClass" tabindex="0"><slot name="nextContent">{{ nextText }}</slot></a>
                </li>
            </ul>

            <div :class="containerClass" v-else>
                <a @click="prevPage()" @keyup.enter="prevPage()" :class="[prevLinkClass, { disabled: firstPageSelected() }]" tabindex="0"><slot name="prevContent">{{ prevText }}</slot></a>
                    <template v-for="page in pages">
                        <a v-if="page.disabled" :class="[pageLinkClass, page.selected ? activeClass : '', { disabled: page.disabled }]" tabindex="0">{{ page.content }}</a>
                        <a v-else @click="handlePageSelected(page.index)" @keyup.enter="handlePageSelected(page.index)" :class="[pageLinkClass, { active: page.selected, disabled: page.disabled }]" tabindex="0">
                        {{ page.content }}
                        </a>
                    </template>
                <a @click="nextPage()" @keyup.enter="nextPage()" :class="[nextLinkClass, { disabled: lastPageSelected() }]" tabindex="0"><slot name="nextContent">{{ nextText }}</slot></a>
            </div>
        </div>
    </div>
</template>
<script>
module.exports = {
    props: {
        columns: Array,
        filterKey: String,
        endPoint: String,
        limit: Number,
        orderBy: String,
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
            default: 'Prev'
        },
        nextText: {
            type: String,
            default: 'Next'
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
            selected: this.initialPage
        }
    },
    computed: {
        filteredData: function () {
            var sortKey = this.sortKey
            var filterKey = this.searchQuery && this.searchQuery.toLowerCase()
            var order = this.sortOrders[sortKey] || 1
            var data = this.data

            // if (filterKey) {
            //     data = this.data
            // }
            // if (sortKey) {
            //     data = data.slice().sort(function (a, b) {
            //         a = a[sortKey]
            //         b = b[sortKey]
            //         return (a === b ? 0 : a > b ? 1 : -1) * order
            //     })
            // }
            return data
        },
        pages: function () {
            let items = {}
            if (this.total <= this.pageRange) {
                for (let index = 0; index < this.total; index++) {
                    let page = {
                        index: index,
                        content: index + 1,
                        selected: index === this.selected
                    }
                        items[index] = page
                }
            } 
            else 
            {
                let leftPart = this.pageRange / 2
                let rightPart = this.pageRange - leftPart
                if (this.selected < leftPart) {
                    leftPart = this.selected
                    rightPart = this.pageRange - leftPart
                } 
                else if (this.selected > this.total - this.pageRange / 2) {
                    rightPart = this.total - this.selected
                    leftPart = this.pageRange - rightPart
                }
                let setPageItem = index => {
                    let page = {
                        index: index,
                        content: index + 1,
                        selected: index === this.selected
                    }
                    items[index] = page
                }
                let setBreakView = index => {
                    let breakView = {
                        content: '...',
                        disabled: true
                    }
                    items[index] = breakView
                }
                // 1st - loop thru low end of margin pages
                for (let i = 0; i < this.marginPages; i++) {
                    setPageItem(i);
                }
                // 2nd - loop thru high end of margin pages
                for (let i = this.total - 1; i >= this.total - this.marginPages; i--) {
                    setPageItem(i);
                }
                // 3rd - loop thru selected range
                let selectedRangeLow = 0;
                if (this.selected - this.pageRange > 0) {
                    selectedRangeLow = this.selected - this.pageRange;
                }
                let selectedRangeHigh = this.total;
                if (this.selected + this.pageRange < this.total) {
                    selectedRangeHigh = this.selected + this.pageRange;
                }
                for (let i = selectedRangeLow; i <= selectedRangeHigh && i <= this.total - 1; i++) {
                    setPageItem(i);
                }
                // Check if there is breakView in the left of selected range
                if (selectedRangeLow > this.marginPages) {
                    setBreakView(selectedRangeLow - 1)
                }
                // Check if there is breakView in the right of selected range
                if (selectedRangeHigh + 1 < this.total - this.marginPages) {
                    setBreakView(selectedRangeHigh + 1)
                }
            }
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
        },
        filterHandler: function(event) {
            console.log(event)
            axios.post(this.endPoint, { filter: this.searchQuery, 
                                        limit: this.limit,
                                        orderBy: this.orderBy })
            .then(response => {
                this.data = response.data.results
                this.total = response.data.total
            })
            .catch(e => {
                this.errors.push(e)
            }) 
        },
        handlePageSelected(selected) {
            if (this.selected === selected) return
            this.selected = selected
            this.clickHandler(this.selected + 1)
        },
        prevPage() {
            if (this.selected <= 0) return
            this.selected--
            this.clickHandler(this.selected + 1)
        },
        nextPage() {
            if (this.selected >= this.pageCount - 1) return
            this.selected++
            this.clickHandler(this.selected + 1)
        },
        firstPageSelected() {
            return this.selected === 0
        },
        lastPageSelected() {
            return (this.selected === this.pageCount - 1) || (this.pageCount === 0)
        }
    },
    created: function(){

        axios.post(this.endPoint, { limit: this.limit })
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

table {
  border: 2px solid #42b983;
  border-radius: 3px;
  background-color: #fff;
}

th {
  background-color: #42b983;
  color: rgba(255,255,255,0.66);
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

td {
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