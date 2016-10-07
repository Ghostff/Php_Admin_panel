db_list: {{
<li class="mix color-1 check1 radio2 option3"><a href="[[link]]">
    <div class="img-hoder"><img src="assets/img/[[image]].jpg" alt="Image 1"></div>
    <div class="name">[[name]]</div> </a>
</li>
}}


nav_list: {{
<li class="filter">
	<a href="[[link]]" data-type="color-1">[[name]]</a>
</li>
}}


alert: {{
<div class="[[type]]-message"><p>[[message]]</p></div>
}}


delete_button: {{
<input type="submit" value="Delete ([[name]])" style="padding: 10px 15px;" name="delete">
}}


add: {{
<div class="fotm-hld cd-main">
    <form class="cd-form floating-labels" method="post">
        <fieldset>
            <legend>[[label]]</legend>
            
            [[alert]]

            <div class="icon">
                <input type="text" name="name" value="[[name]]" placeholder="Save As">
            </div> 
            <div class="icon">
                <input type="text" name="host" value="[[host]]" placeholder="Hostname (default: localhost)">
            </div> 
            <div class="icon">
                <input type="text" name="user" value="[[user]]" placeholder="Username">
            </div> 

            <div class="icon">
                <input type="text" name="pass" value="[[pass]]" placeholder="Password">
            </div>
        </fieldset>
        <div>
            [[delete_button]]
            <input type="submit" value="Save" style="padding: 10px 15px;margin-right:20px;" name="save">
            <input type="submit" value="Test" style="padding: 10px 15px;margin-right:20px;" name="test">
        </div>
</form>
</div>
}}


table_list: {{
<table width="100%" border="1">
	<tbody>
        <tr>
            [[table_head]]
        </tr>
        [[contents]]
    </tbody>
</table>
}}
