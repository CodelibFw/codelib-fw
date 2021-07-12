Vue.component('step-2', function (resolve, reject) {
        loadRemote('install/html/step2.html', {action:""}, function(r) {
            resolve({
                template: '<div>' + r + '</div>',
                data () {
                    return {
                        feedback: '',
                        maxlg: 100,
                        valid: true,
                        name: 'My CL App',
                        path: '',
                        pathRules: [
                            v => !!v || 'Name is required',
                            v => (v && v.length <= this.maxlg) || 'Name must be less than '+this.maxlg+' characters',
                            v => v && v.indexOf(" ") === -1 || 'No spaces allowed'
                        ],
                        apptype: 'at1',
                        select: null,
                        items: [
                            'Item 1',
                            'Item 2',
                            'Item 3',
                            'Item 4'
                        ],
                        reglogin: false,
                        mysql: false,
                    }
                },
                mounted() {
                    this.$root.cancontinue = false;
                },
                methods: {
                    createapp () {
                        if (this.$refs.form.validate()) {
                            var self = this;
                            loadRemote('install/Wzd.php', {action:"create", src: this.path, lg: this.reglogin, mysql: this.mysql, apptype: this.apptype}, function(r) {
                                self.feedback = r.feedback;
                                self.$root.cancontinue = true;
                            }, function (e) {
                                self.feedback = 'An error occurred: ' + e;
                            });
                        }
                    },
                    reset () {
                        this.reglogin = false;
                        this.mysql = false;
                        this.path = '';
                        this.apptype = 'at1';
                    },
                    resetValidation () {
                        this.$refs.form.resetValidation()
                    }
                }
            })
        }, reject);
    });
