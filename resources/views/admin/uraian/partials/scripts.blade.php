 <script>
   $(function() {
     const keyname = window.location.href;
     const data = JSON.parse(localStorage.getItem(keyname));

     if (data && data.category_id) {
       $('#category').val(data.category_id);
       $('#category').trigger('change');
       const option = $('#category').find(`option[value=${data.category_id}]`).attr('selected', 'selected');
       $('#stay-in-category').prop('checked', true);
     }

     $('#category').on('change', function(e) {
       const data = JSON.parse(localStorage.getItem(keyname));
       if (data && data.category_id) {
         localStorage.setItem(keyname, JSON.stringify({
           category_id: e.target.value
         }))
       }
     });

     $('#stay-in-category').on('change', function(e) {
       const element = $(this);
       const categoryId = $('#category').val();
       if (element.is(':checked')) {
         localStorage.setItem(keyname, JSON.stringify({
           category_id: categoryId
         }));
       } else {
         localStorage.setItem(keyname, JSON.stringify({
           category_id: null
         }));
       }
     });

     $('.uraian-datatable').DataTable({
       ordering: false,
       language: {
         url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
       }
     });

     $('#treeview').jstree({
       core: {
         themes: {
           responsive: false
         }
       },
       types: {
         default: {
           icon: 'fa fa-folder text-warning'
         },
         file: {
           icon: 'fa fa-file text-warning'
         }
       },
       plugins: ['types']
     });

     // handle link clicks in tree nodes(support target="_blank" as well)
     $('#treeview').on('select_node.jstree', function(e, data) {
       var link = $('#' + data.selected).find('a');
       if (link.attr("href") != "#" && link.attr("href") != "javascript:;" && link.attr("href") != "") {
         if (link.attr("target") == "_blank") {
           link.attr("href").target = "_blank";
         }
         document.location.href = link.attr("href");
         return false;
       }
     });

     $('.uraian-datatable').on('click', 'tbody .btn-delete', function() {
       Swal.fire({
         title: 'Apakah kamu yakin?',
         text: 'Form menu uraian & isi uraian yang dihapus tidak bisa dikembalikan!',
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#fc544b',
         cancelButtonColor: '#3490dc',
         confirmButtonText: 'Hapus',
         cancelButtonText: 'Batal',
       }).then((result) => {
         if (result.isConfirmed) {
           $('#form-delete').prop('action', $(this).data('url'))
           $('#form-delete').submit()
         }
       })
     });
   });
 </script>
