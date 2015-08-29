var pum_timeout	= 2500;
var pum_closetimer = 0;
var pum_menuitem = 0;

// open hidden layer
function mopen(id,xpos,ypos)
{	
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if (pum_menuitem) pum_menuitem.style.display = 'none';

	// get new layer and show it
	pum_menuitem = document.getElementById(id);
	pum_menuitem.style.left = xpos+'px';
	pum_menuitem.style.top = ypos+'px';
	pum_menuitem.style.display = 'block';
	mclosetime();

}
// close showed layer
function mclose()
{
	if (pum_menuitem) pum_menuitem.style.display = 'none';
}

// go close timer
function mclosetime()
{
	pum_closetimer = window.setTimeout(mclose, pum_timeout);
}

// cancel close timer
function mcancelclosetime()
{
	if (pum_closetimer)
	{
		window.clearTimeout(pum_closetimer);
		pum_closetimer = null;
	}
}

// close layer when click-out
//document.onclick = mclose; 