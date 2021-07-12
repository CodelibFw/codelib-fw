Vue.component('step-1', {template: '<div><h1>Checking requirements</h1><div>{{ feedback }}</div></div>',
    data () {
        return {
            feedback: ''
        }
    },
    mounted() {
        var self = this;
        loadRemote('index.php', {action:"reqs"}, function(r) {
            self.feedback = r.feedback;
        }, function (e) {
            self.feedback = 'An error occurred: ' + e;
        });
    },
});
