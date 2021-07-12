Vue.component('introspective', function (resolve, reject) {
    loadRemote('install/html/Introspective.html', {action:""}, function(r) {
        resolve({
            template: '<div>' + r + '</div>',
            data () {
                return {
                    feedback: '',
                    maxlg: 50,
                    valid: true,
                    options: false,
                    path: this.$store.state.apppath,
                    reglogin: false,
                    mysql: false,
                }
            },
            methods: {
                connect () {
                    var self = this;
                    if (this.path == null || this.path == '') {
                        this.feedback = 'Path to App not specified, cannot continue';
                        return;
                    }
                    loadRemote('install/Wzd.php', {action:"diagn", src: this.path}, function(r) {
                        self.feedback = r.feedback;
                    }, function (e) {
                        self.feedback = 'An error occurred: ' + e;
                    });
                    self.options = true;
                },
                newflow () {

                },
                mysqlconn () {

                },
                reset () {
                    this.$refs.form.reset()
                },
                resetValidation () {
                    this.$refs.form.resetValidation()
                }
            }
        })
    }, reject);
});
