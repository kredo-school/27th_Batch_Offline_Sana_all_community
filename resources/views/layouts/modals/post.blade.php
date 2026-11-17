<style>
.btn-act{
  background-color: #A8CF45 !important;
}

.btn-com{
  background-color: #F58634 !important;
}
</style>
<div class="modal fade" id="modal-post" tabindex="-1" aria-hidden="true">
    @switch('__translation_driven_modal__')
        @case('ja')
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header border-success bg-primary">
                    <h1 class="h1 mb-0 mt-0 modal-title text-white d-flex align-items-center w-100 justify-content-center ms-4" id="modal-post-title">
                        <span class="badge mx-auto">新規作成</span> 
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3 class="text-bold">どちらを作成しますか？</h3>
                </div>
                <div class="modal-footer border-0 align-items-center d-flex mx-auto">
                    <a href="{{ route('events.create')}}" class="btn btn-act text-white ps-5 pe-5">イベント</a>
                    <a href="{{ route('community.create')}}" class="btn btn-com text-white ps-4 pe-4">コミュニティ</a>
                </div>
            </div>
        </div>
            @break
        @default
            <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header border-success bg-primary">
                    <h1 class="h1 mb-0 mt-0 modal-title text-white d-flex align-items-center w-100 justify-content-center ms-4" id="modal-post-title">
                        <span class="badge mx-auto">{{ __('Create') }}</span>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3 class="text-bold">{{ __('Which do you want to create?') }}</h3>
                </div>
                <div class="modal-footer border-0 align-items-center d-flex mx-auto">
                    <a href="{{ route('events.create')}}" class="btn btn-act text-white ps-5 pe-5">{{ __('Activity') }}</a>
                    <a href="{{ route('community.create')}}" class="btn btn-com text-white ps-4 pe-4">{{ __('Community') }}</a>
                </div>
            </div>
        </div>
            @break
       
    @endswitch
        
    </div>