Vue.component('todo-comp', {
    template: '' +
        '<div v-if="page==0">&nbsp;</div>' +
        '<div v-else-if="page==1"><step-1></step-1></div>' +
        '<div v-else-if="page==2"><step-2></step-2></div>' +
        '<div v-else-if="page==5"><introspective></introspective></div>',
    data () {
        return {
            //
        }
    },

    methods: {
        start() {
            return '';
        }
    },
    props:["page"]
});
Vue.component('async-comp', function (resolve, reject) {
    //resolve({template: '<div>I am async!</div>'});
    axios.get('index.php?comp=hc1')
        .then(function (response) {
            resolve(response.data);
            console.log(response);
        })
        .catch(function (error) {
            reject('error loading component');
            console.log(error);
        })
        .then(function () {
            // always executed
        });

});
Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        step: 0,
        apppath: ''
    },
    mutations: {
        increment (state) {
            state.step++
        }
    }
});

new Vue({ el: '#app',
    store: store,
    data () {
        return {
            currstep: this.$store.state.step,
            btnaction: 'Start',
            apppath: '',
            cancontinue: true,
            diagstep: 5
        }
    },
    methods: {
        next() {
            if (this.apppath === '') {
                this.$store.commit('increment');
                this.currstep = this.$store.state.step;
                this.btnaction = 'Next';
            } else {
                this.$store.state.step = this.diagstep;
                this.$store.state.apppath = this.apppath;
                this.currstep = this.$store.state.step;
                this.btnaction = 'Ok';
            }
        }
    },
});
function loadRemote(url, postdata, success, failure) {
    axios.post(url, postdata)
        .then(response => {
            console.log(response);
            success(response.data);
        })
        .catch(function (error) {
            console.log(error.message);
            failure(error.message);
        });
}
