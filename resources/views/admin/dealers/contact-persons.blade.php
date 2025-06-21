<hr class="bold-hr">
<div class="form-group">
    <label class="col-md-2 control-label">Contact Persons</label>
    <div class="col-md-10">
        <table id="ProductSearchRow" class="table table-hover table-bordered table-striped">
            <tbody>
                <tr>
                    <th>Designation</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
               
                @if(!empty($dealerdata) && !empty($dealerdata['contact_persons']))
                @foreach($dealerdata['contact_persons'] as $dealerkey=> $dealerContactPerson)
                <tr class="blockIdWrap">
                    <td>
                        <select class="form-control" name="designations[]" required>
                            <option value="">Please Select</option>
                            @foreach($designationsArr as $designation)
                            <option value="{{$designation}}" @if($dealerContactPerson['designation']== $designation) selected @endif>{{$designation}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" placeholder="Name" name="names[]" class="form-control" required value="{{$dealerContactPerson['name']}}">
                    </td>
                    <td>
                        <input type="number" placeholder="Mobile" name="mobiles[]" class="form-control" required value="{{$dealerContactPerson['mobile']}}">
                    </td>
                    <td>
                        <input type="emails[]" placeholder="Email" name="emails[]" class="form-control" required value="{{$dealerContactPerson['email']}}">
                    </td>
                    <td>
                        <a title="Remove" class="btn btn-sm red AssignRowRemove" href="javascript:;"> <i class="fa fa-times"></i></a>
                        
                    </td>
                </tr>
                @endforeach
                @else
                @for ($i=1; $i <=0; $i++)
                <tr class="blockIdWrap">
                    <td>
                        <select class="form-control" name="designations[]" required>
                            <option value="">Please Select</option>
                            <option value="Owner">Owner</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" placeholder="Name" name="names[]" class="form-control" required>
                    </td>
                    <td>
                        <input type="number" placeholder="Mobile" name="mobiles[]" class="form-control" required>
                    </td>
                    <td>
                        <input type="emails[]" placeholder="Email" name="emails[]" class="form-control" required>
                    </td>
                    <td></td>
                </tr>
                @endfor
                @endif
            </tbody>
        </table>
        <input type="button" id="addAssignRow" value="Add More" />
    </div>
</div>
<hr class="bold-hr">