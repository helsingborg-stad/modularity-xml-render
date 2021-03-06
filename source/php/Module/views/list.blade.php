<div class="{{ $classes }}">
    @if (!$hideTitle && !empty($post_title))
        <h4 class="box-title">{!! apply_filters('the_title', $post_title) !!}</h4>
    @endif
    <div class="modularity-xml-render"
         data-url="{{ $url }}"
         data-view="{{ $view }}"
         data-field-map="{{ $fieldMap }}"
         data-show-search="{{ $show_search ? true : false }}"
         data-show-pagination="{{ $show_pagination ? true : false }}"
         data-per-page="{{ $per_page ?? 10 }}">
    </div>
</div>