document.addEventListener("DOMContentLoaded", function () {
  const picker = document.getElementById("dayPicker");
  if (picker) {
    picker.addEventListener("change", function () {
      const d = picker.value;
      window.location.search = "?day=" + d;
    });
  }
});
