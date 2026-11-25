<label class="checkbox-field">
    <input type="checkbox" name="privacy" value="1" @checked(old('privacy')) required @error('privacy') aria-invalid="true" aria-describedby="privacy-error" @enderror>
    <span>{{ __('site.privacy_agree') }} <a href="{{ route('public.privacy', ['locale' => $locale]) }}" target="_blank" rel="noopener">{{ __('site.privacy') }}</a>.</span>
</label>
@error('privacy')<p class="error" id="privacy-error">{{ $message }}</p>@enderror

