let menu = document.querySelector('#menu-icon');
let navbar = document.querySelector('navbar');

menu.onclick ()  {
	menu.classlist.toggle('bx-menu');
	navbar.classlist.toggle('active');
}

window.onscroll (){
	menu.classlist.remove('bx-x');
	navbar.classlist.remove('active');
}