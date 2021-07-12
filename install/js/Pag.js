Vue.component('cl-pag', {template: '' +
        '<div class="text-xs-center">\n' +
        '    <v-container>\n' +
        '    <v-layout justify-center>\n' +
        '<v-flex xs8>\n' +
        '<v-card>\n' +
        '<v-card-text>\n' +
        '<v-pagination\n' +
        'v-model="page"\n' +
        ':length="np" disabled\n' +
        '    ></v-pagination>\n' +
        '    </v-card-text>\n' +
        '    </v-card>\n' +
        '    </v-flex>\n' +
        '    </v-layout>\n' +
        '    </v-container>\n' +
        '    </div>\n',
        data () {
            return {
                //np: 3,
            }
        },
        props: ['np', 'page']
}
        );
