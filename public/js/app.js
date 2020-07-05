// this will recursively display comments with their replies
var Comments = {
	props: ['comments'],
	name: 'comments',
	template: '<div>' +
		'<p class="card-text" v-for="comment in comments" style="margin-left: 10px">' +
			'{{comment.user.personaname + \' - \'}}' +
			'<small class="text-muted">{{comment.text}}</small>' +
			'<a href="#" style="float:right" @click.prevent="replyToComment(comment.id)">reply</a>' +
			'<comments v-if="comment.replies.length > 0" :comments="comment.replies" @reply="replyToComment"></comments>' +
		'</p>' +
	'</div>',
	methods: {
		//this will relay the click event to the parent component through all recursion levels
		replyToComment: function(id) {
			this.$emit('reply', id);
		}
	},
};

var app = new Vue({
	el: '#app',
	components: {
		Comments
	},
	data: {
		login: '',
		pass: '',
		post: false,
		selectedComment: false,
		invalidComment: false,
		invalidLogin: false,
		invalidCredentials: false,
		invalidPass: false,
		invalidSum: false,
		posts: [],
		addSum: 0,
		amount: 0,
		likes: 0,
		commentText: '',
		packs: [
			{
				id: 1,
				price: 5
			},
			{
				id: 2,
				price: 20
			},
			{
				id: 3,
				price: 50
			},
		],
	},
	computed: {
		test: function () {
			var data = [];
			return data;
		}
	},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})
	},
	methods: {
		logout: function () {
			console.log ('logout');
		},
		logIn: function () {
			var self= this;
			if(self.login === ''){
				self.invalidLogin = true
			}
			else if(self.pass === ''){
				self.invalidLogin = false
				self.invalidPass = true
			}
			else{
				self.invalidLogin = false
				self.invalidPass = false
				var formData = new FormData;
				formData.set('login', self.login);
				formData.set('password', self.pass);
				axios.post('/main_page/login', formData)
					.then(function (response) {
						if (response.data.status === 'error') {
							self.invalidCredentials = true;
							return;
						}

						location.reload();
					})
			}
		},
		//select the comment we want to reply to
		replyToComment: function(id) {
			this.selectedComment = id;
		},
		addComment: function() {
			var self = this;

			var url = '/main_page/comment/' + this.post.id;
			if (this.selectedComment) { //if a comment is selected add its id in the url
				url += '/' + this.selectedComment;
			}

			var data = new FormData();
			data.set('text', this.commentText);
			axios.post(url, data)
				.then(function(response) {
					if (response.data.status === 'error') {
						self.invalidComment = true;
						return;
					}

					//update the post to rerender and reset the comment field
					self.post = response.data.post;
					self.commentText = '';
				});
		},
		fiilIn: function () {
			var self= this;
			if(self.addSum === 0){
				self.invalidSum = true
			}
			else{
				self.invalidSum = false
				var data = new FormData();
				data.set('amount', this.addSum);
				axios.post('/main_page/add_money', data)
					.then(function (response) {
						if (response.data.status === 'error') {
							self.invalidSum = true;
							return;
						}

						setTimeout(function () {
							$('#addModal').modal('hide');
						}, 500);
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					if(self.post){
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		addLike: function (id) {
			var self = this;
			var url = '/main_page/like_post/' + id;
			axios.post(url)
				.then(function (response) {
					self.likes = response.data.likes;
				})

		},
		buyPack: function (id) {
			var self = this;
			var url = '/main_page/buy_boosterpack/' + id;
			axios.post(url)
				.then(function (response) {
					self.amount = response.data.amount
					if(self.amount !== 0){
						setTimeout(function () {
							$('#amountModal').modal('show');
						}, 500);
					}
				})
		}
	}
});

