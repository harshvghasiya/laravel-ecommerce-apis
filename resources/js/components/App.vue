<template>
	<Header />
	<Sidebar />
	<Footer />
</template>

<script>
	
	import Header from './layouts/Header.vue'
	import Sidebar from './layouts/Sidebar.vue'
	import Footer from './layouts/Footer.vue'
	
	export default{

		name:'App',
		components:{

			Header,Footer,Sidebar

		}
	}

</script>