@extends('layouts.app')

@section('title', 'Home')

@section('content')
<style>
.home-back {
  position: relative;
}

.home-back img {
  display: block;
  width: 100%;
  height: auto;
}

.home-controls {
  position: absolute;
  top: 50%;
  left: 50%;
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: min(calc(100% - 32px), 520px);
  padding: 16px;
  transform: translate(-50%, -50%);
  /* background: rgba(0, 0, 0, 0.32); */
  border-radius: 8px;
}

.home-search {
  display: flex;
  width: 100%;
  margin: 0;
}

.home-search .search {
  display: flex;
  width: 100%;
}

.home-search input {
  min-width: 0;
  flex: 1;
}

.home-actions {
  display: flex;
  gap: 12px;
}

.home-action {
  flex: 1;
}

.btn-act{
  background-color: #A8CF45 !important;
}

.btn-com{
  background-color: #F58634 !important;
}

@media (max-width: 420px) {
  .home-controls {
    gap: 8px;
    padding: 10px;
  }

  .home-actions {
    gap: 8px;
  }

  .home-action {
    padding: 6px 8px;
    font-size: 0.8rem;
  }
}
</style>
     
    <section class="home-back">
      <img class="img-fluid w-100" src="{{ asset('images/TopPicture.png') }}" alt="Sana All Community">
      <div class="home-controls">
        <form class="toolbar home-search" method="GET" action="#">
          <div class="search">
            <label class="visually-hidden" for="search">Search</label>
            <input class="form-control" id="search" type="search" name="search" value="{{ request('search') }}"
              placeholder="Search">
            <button type="submit" aria-label="Search" title="Search" class="btn btn-primary">
              <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
            </button>
          </div>
        </form>
        <div class="home-actions">
          <a href="{{ route('events.index')}}" class="btn btn-act text-white home-action border border-radius rounded-white">ACTIVITY</a>
          <a href="{{ route('community.index')}}" class="btn btn-com text-white home-action border border-radius rounded-white">COMMUNITY</a>
        </div>
      </div>
    </section>
@endsection