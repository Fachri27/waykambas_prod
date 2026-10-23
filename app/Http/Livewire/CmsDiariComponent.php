<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Intervention\Image\ImageManager;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class CmsDiariComponent extends Component
{
    use WithPagination;
    use WithTranslation;
    public $deleteName, $deleteID, $deleter;
    public $dataField = 'publishdate', $dataOrder = 'desc', $paginate = 7, $search = '';

    public function sortingField($field){
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }
    public function getDiary(){
        $sc = '%' . $this->search . '%';
        try {
            return  DB::table('greendiary')
                        ->select('id', 'titleEN', 'titleID', 'img', 'isActive')
                        ->where(function ($query) use ($sc) {
                            $query->where('titleEN', 'like', $sc)->orWhere('titleID', 'like', $sc);
                        })
                        ->orderBy($this->dataField, $this->dataOrder)
                        ->paginate($this->paginate);
        } catch (\Throwable $th) {
            return [];
        }
    }
    // refresh page on search
    public function updatedSearch(){
        $this->resetPage();
    }
    public function closeDelete(){
        $this->deleter = false;
        $this->deleteName = null;
        $this->deleteID = null;
    }
    public function delete($id){

        //load data to delete function
        $dataDelete = DB::table('greendiary')->where('id', $id)->first();
        $this->deleteName = $dataDelete->titleEN ?: $dataDelete->titleID;
        $this->deleteID = $dataDelete->id;

        $this->deleter = true;
    }
    public function deleting($id){
        DB::table('greendiary')->where('id', $id)->delete();

        $message = 'Successfully deleting internal news';
        $type = 'success'; //error, success
        $this->emit('toast',$message, $type);


        $this->closeDelete();
    }
    // diaries whose English title or image description is still missing, or only a copy of the Indonesian text
    public function untranslatedDiaries(){
        return DB::table('greendiary')->where(function ($query) {
            $query->where('titleEN', '')->orWhereNull('titleEN')->orWhereColumn('titleEN', 'titleID')
                ->orWhere('imgDescEN', '')->orWhereNull('imgDescEN')->orWhereColumn('imgDescEN', 'imgDescID');
        });
    }

    // the same test in php, case-insensitive like the database comparison above
    protected function needsTranslation($en, $id){
        return blank($en) || Str::lower(trim($en)) === Str::lower(trim($id));
    }

    // fills the missing English titles and descriptions from the Indonesian ones, real English text is kept
    public function translateAll(){
        if (!session('id')) {
            abort(403);
        }
        set_time_limit(600);
        $done = 0;
        // 25 diaries per round keeps each DeepL request within its 50 texts
        foreach ($this->untranslatedDiaries()->orderBy('id')->get(['id', 'titleID', 'titleEN', 'imgDescID', 'imgDescEN'])->chunk(25) as $diaries) {
            $texts = [];
            foreach ($diaries as $diary) {
                if ($this->needsTranslation($diary->titleEN, $diary->titleID) && filled($diary->titleID)) {
                    $texts[$diary->id.'.title'] = $diary->titleID;
                }
                if ($this->needsTranslation($diary->imgDescEN, $diary->imgDescID) && filled($diary->imgDescID)) {
                    $texts[$diary->id.'.imgDesc'] = $diary->imgDescID;
                }
            }
            $translated = $this->translateOrToast(fn ($translator) => $translator->toEnglishMany($texts));
            if ($translated === null) {
                break;
            }
            foreach ($diaries as $diary) {
                $update = [];
                if (isset($translated[$diary->id.'.title'])) {
                    $update['titleEN'] = $translated[$diary->id.'.title'];
                    $update['slugEN'] = Str::slug($update['titleEN'], '-');
                }
                if (isset($translated[$diary->id.'.imgDesc'])) {
                    $update['imgDescEN'] = $translated[$diary->id.'.imgDesc'];
                }
                if ($update) {
                    DB::table('greendiary')->where('id', $diary->id)->update($update + ['updated_at' => Carbon::now('Asia/Jakarta')]);
                    $done++;
                }
            }
        }
        if ($done) {
            $this->emit('toast', 'Translated '.$done.' '.Str::plural('diary', $done).' into English, please check them', 'success');
        }
    }

    public function render()
    {
        $diary = $this->getDiary();
        foreach ($diary as $item) {
            $item->missingEnglish = $this->needsTranslation($item->titleEN, $item->titleID);
        }
        $untranslated = $this->untranslatedDiaries()->count();
        return view('livewire.cms-diari-component', compact('diary', 'untranslated'));
    }

}
