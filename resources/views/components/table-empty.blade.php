@props(['cols' => 5, 'title' => 'Data tidak ditemukan', 'text' => 'Coba ubah kata kunci atau filter pencarian.'])
<div data-table-empty hidden><x-empty-state icon="search" :title="$title" :text="$text"/></div>
