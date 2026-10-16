import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import { SORT_OPTIONS, type SortValue } from '@/lib/sort';

interface Props {
    value: SortValue;
    onChange: (value: SortValue) => void;
}

/** "Sort by" dropdown for the request list. */
export default function SortControl({ value, onChange }: Props) {
    return (
        <div className="flex items-center gap-2">
            <Label htmlFor="sort-by" className="whitespace-nowrap">
                Sort by
            </Label>
            <NativeSelect id="sort-by" className="w-auto" value={value} onChange={(e) => onChange(e.target.value as SortValue)}>
                {SORT_OPTIONS.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </NativeSelect>
        </div>
    );
}
