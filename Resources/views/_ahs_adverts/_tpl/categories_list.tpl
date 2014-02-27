<div id="categories">
	<h4>Kategorie:</h4>
	<ul>
		{{ foreach from=$categories item=category }}
			<li>
                <a href="{{ generate_url route="ahs_advertsplugin_default_category" parameters=['id'=>$category->getId(), 'slug'=>$category->getSlug()] }}">
                    <img src="/public/bundles/ahsadvertsplugin/images/icons/{{ $category->getId() }}.png" alt="{{ $category->getName() }}" />
                    <span>{{ $category->getName() }}</span>
                </a>
                <div style="clear:both"></div>
            </li>
		{{ /foreach }}
	</ul>
</div>