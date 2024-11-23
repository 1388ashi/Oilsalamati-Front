<div class="dashboard-tab">
  <ul class="nav nav-tabs flex-lg-column border-bottom-0" id="top-tab" role="tablist">
    <li class="nav-item">
      <a data-bs-toggle="tab" data-bs-target="#info" class="nav-link active">
        <i class="fe fe-user"></i>
        <span>حساب کاربری</span>
      </a>
    </li>
    <li class="nav-item">
      <a data-bs-toggle="tab" data-bs-target="#address" class="nav-link">
        <i class="fe fe-map"></i>
        <span>لیست آدرس ها</span>
      </a>
    </li>
    <li class="nav-item">
      <a data-bs-toggle="tab" data-bs-target="#orders" class="nav-link">
        <i class="fe fe-clipboard"></i>
        <span>سفارشات من</span>
      </a>
    </li>
    <li class="nav-item">
      <a data-bs-toggle="tab" data-bs-target="#favorites" class="nav-link">
        <i class="fe fe-heart"></i>
        <span>لیست علاقه مندی ها</span>
      </a>
    </li>
    <li class="nav-item">
      <a data-bs-toggle="tab" data-bs-target="#wallet" class="nav-link">
        <i class="fe fe-dollar-sign"></i>
        <span>کیف پول</span>
      </a>
    </li>
    <li class="nav-item">
      <a onclick="$('#LogoutForm').submit()" class="nav-link btn-link" id="log-out-button">
        <i class="fe fe-log-out"></i>
        <span>خروج از سیستم</span>
      </a>
      <form action="{{ route('customer.logout') }}" id="LogoutForm" method="POST">
        @csrf
      </form>
    </li>
  </ul>
</div>