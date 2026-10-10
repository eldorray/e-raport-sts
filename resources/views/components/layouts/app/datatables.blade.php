{{-- jQuery + DataTables untuk tabel di halaman web (dipakai juga dalam mode aplikasi HP) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const tableIds = ['#subjects-table', '#guru-table', '#siswa-table', '#kelas-table', '#rombel-table',
            '#guru-subjects-table', '#rapor-table', '#tahfidz-table'
        ];
        tableIds.forEach((id) => {
            const tableEl = document.querySelector(id);
            if (tableEl) {
                new DataTable(tableEl);
            }
        });
    });
</script>
