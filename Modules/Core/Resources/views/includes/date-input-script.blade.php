<script>
  $('#' + "{{ $textInputId }}").MdPersianDateTimePicker({
    targetDateSelector: '#' + "{{ $dateInputId }}",
    targetTextSelector: '#' + "{{ $textInputId }}",
    englishNumber: false,
    toDate:true,
    enableTimePicker: true,
    dateFormat: 'yyyy-MM-dd HH:mm:ss',
    textFormat: 'yyyy-MM-dd HH:mm:ss',
    groupId: 'rangeSelector1',
  });
</script>
